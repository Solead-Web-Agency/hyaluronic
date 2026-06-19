<?php
/**
 * OrderEdit
 *
 * @author    silbersaiten <info@silbersaiten.de>
 * @copyright 2022 silbersaiten
 * @license   See joined file licence.txt
 * @support   silbersaiten <support@silbersaiten.de>
 * @category  Module
 * @version   2.0.31
 * @link      https://www.silbersaiten.de
 */

namespace OrderEdit\Controller;

use Address;
use Carrier;
use Cart;
use Db;
use Employee;
use Order;
use OrderState;
use PrestaShop\PrestaShop\Adapter\Entity\Tax;
use PrestaShop\PrestaShop\Core\Domain\Order\Command\AddCartRuleToOrderCommand;
use PrestaShop\PrestaShop\Core\Domain\Order\Command\DeleteCartRuleFromOrderCommand;
use PrestaShop\PrestaShop\Core\Domain\ValueObject\QuerySorting;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Controller\Admin\Sell\Order\OrderController;
use PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButtonsCollection;
use PrestaShopBundle\Form\Admin\Type\FormattedTextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Exception;
use InvalidArgumentException;
use PrestaShop\PrestaShop\Core\Domain\CartRule\Exception\InvalidCartRuleDiscountValueException;
use PrestaShop\PrestaShop\Core\Domain\CustomerMessage\Exception\CustomerMessageConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Order\Command\UpdateOrderStatusCommand;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\CannotEditDeliveredOrderProductException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\CannotFindProductInOrderException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\ChangeOrderStatusException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\DuplicateProductInOrderException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\DuplicateProductInOrderInvoiceException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\InvalidAmountException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\InvalidCancelProductException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\InvalidOrderStateException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\InvalidProductQuantityException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\NegativePaymentAmountException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderEmailSendException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Order\Product\Command\AddProductToOrderCommand;
use PrestaShop\PrestaShop\Core\Domain\Order\Query\GetOrderForViewing;
use PrestaShop\PrestaShop\Core\Domain\Order\QueryResult\OrderForViewing;
use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;
use PrestaShop\PrestaShop\Core\Domain\Product\Exception\ProductOutOfStockException;
use PrestaShop\PrestaShop\Core\Order\OrderSiblingProviderInterface;
use PrestaShop\PrestaShop\Core\Search\Filters\OrderFilters;
use PrestaShopBundle\Exception\InvalidModuleException;
use PrestaShopBundle\Form\Admin\Sell\Customer\PrivateNoteType;
use PrestaShopBundle\Form\Admin\Sell\Order\AddOrderCartRuleType;
use PrestaShopBundle\Form\Admin\Sell\Order\AddProductRowType;
use PrestaShopBundle\Form\Admin\Sell\Order\ChangeOrderAddressType;
use PrestaShopBundle\Form\Admin\Sell\Order\ChangeOrderCurrencyType;
use PrestaShopBundle\Form\Admin\Sell\Order\EditProductRowType;
use PrestaShopBundle\Form\Admin\Sell\Order\OrderMessageType;
use PrestaShopBundle\Form\Admin\Sell\Order\OrderPaymentType;
use PrestaShopBundle\Form\Admin\Sell\Order\UpdateOrderShippingType;
use PrestaShopBundle\Form\Admin\Sell\Order\UpdateOrderStatusType;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderEditController extends FrameworkBundleAdminController
{
    const DEFAULT_PRODUCTS_NUMBER = 8;
    const PRODUCTS_PAGINATION_OPTIONS = [8, 20, 50, 100];
    /**
     * @var OrderController
     */
    private $decoratedController;

    public function __construct($decoratedController)
    {
        parent::__construct();
        $this->decoratedController = $decoratedController;
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     *
     * @param int $orderId
     * @param Request $request
     *
     * @return Response
     */
    public function viewAction(int $orderId, Request $request): Response
    {
        try {
            /** @var OrderForViewing $orderForViewing */
            $orderForViewing = $this->decoratedController->getQueryBus()->handle(new GetOrderForViewing($orderId));
        } catch (OrderException $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));
            return $this->redirectToRoute('admin_orders_index');
        }

        $formFactory = $this->decoratedController->get('form.factory');
        $updateOrderStatusForm = $formFactory->createNamed(
            'update_order_status',
            UpdateOrderStatusType::class, [
                'new_order_status_id' => $orderForViewing->getHistory()->getCurrentOrderStatusId(),
            ]
        );
        $updateOrderStatusActionBarForm = $formFactory->createNamed(
            'update_order_status_action_bar',
            UpdateOrderStatusType::class, [
                'new_order_status_id' => $orderForViewing->getHistory()->getCurrentOrderStatusId(),
            ]
        );

        $addOrderCartRuleForm = $this->decoratedController->createForm(AddOrderCartRuleType::class, [], [
            'order_id' => $orderId,
        ]);
        $addOrderPaymentForm = $this->decoratedController->createForm(OrderPaymentType::class, [
            'id_currency' => $orderForViewing->getCurrencyId(),
        ], [
            'id_order' => $orderId,
        ]);

        if (version_compare(_PS_VERSION_, '1.7.7.6', '<')) {
            $_id_lang_param = [];
        } else {
            $_id_lang_param = ['lang_id' => $orderForViewing->getCustomer()->getLanguageId()];
        }

        $orderMessageForm = $this->decoratedController->createForm(OrderMessageType::class, $_id_lang_param, [
            'action' => $this->decoratedController->generateUrl('admin_orders_send_message', ['orderId' => $orderId]),
        ]);
        $orderMessageForm->handleRequest($request);

        $changeOrderCurrencyForm = $this->decoratedController->createForm(ChangeOrderCurrencyType::class, [], [
            'current_currency_id' => $orderForViewing->getCurrencyId(),
        ]);

        $changeOrderAddressForm = null;
        $privateNoteForm = null;

        if (null !== $orderForViewing->getCustomer()) {
            $changeOrderAddressForm = $this->decoratedController->createForm(ChangeOrderAddressType::class, [], [
                'customer_id' => $orderForViewing->getCustomer()->getId(),
            ]);

            $privateNoteForm = $this->decoratedController->createForm(PrivateNoteType::class, [
                'note' => $orderForViewing->getCustomer()->getPrivateNote(),
            ]);
        }

        $updateOrderShippingForm = $this->decoratedController->createForm(UpdateOrderShippingType::class, [
            'new_carrier_id' => $orderForViewing->getCarrierId(),
        ], [
            'order_id' => $orderId,
        ]);

        $currencyDataProvider = $this->decoratedController->container->get('prestashop.adapter.data_provider.currency');
        $orderCurrency = $currencyDataProvider->getCurrencyById($orderForViewing->getCurrencyId());

        $addProductRowForm = $this->decoratedController->createForm(AddProductRowType::class, [], [
            'order_id' => $orderId,
            'currency_id' => $orderForViewing->getCurrencyId(),
            'symbol' => $orderCurrency->symbol,
        ]);
        $editProductRowForm = $this->decoratedController->createForm(EditProductRowType::class, [], [
            'order_id' => $orderId,
            'symbol' => $orderCurrency->symbol,
        ]);

        $formBuilder = $this->decoratedController->get('prestashop.core.form.identifiable_object.builder.cancel_product_form_builder');
        $backOfficeOrderButtons = new ActionsBarButtonsCollection();

        try {
            $this->decoratedController->dispatchHook(
                'actionGetAdminOrderButtons', [
                'controller' => $this,
                'id_order' => $orderId,
                'actions_bar_buttons_collection' => $backOfficeOrderButtons,
            ]);

            $cancelProductForm = $formBuilder->getFormFor($orderId);
        } catch (Exception $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));

            return $this->redirectToRoute('admin_orders_index');
        }

        $this->handleOutOfStockProduct($orderForViewing);

        $merchandiseReturnEnabled = (bool) $this->configuration->get('PS_ORDER_RETURN');

        /** @var OrderSiblingProviderInterface $orderSiblingProvider */
        $orderSiblingProvider = $this->decoratedController->get('prestashop.adapter.order.order_sibling_provider');

        $paginationNum = (int) $this->configuration->get('PS_ORDER_PRODUCTS_NB_PER_PAGE', self::DEFAULT_PRODUCTS_NUMBER);
        $paginationNumOptions = self::PRODUCTS_PAGINATION_OPTIONS;
        if (!in_array($paginationNum, $paginationNumOptions)) {
            $paginationNumOptions[] = $paginationNum;
        }
        sort($paginationNumOptions);

        $editProductRowForm->add('product_name', FormattedTextareaType::class, [
            'label' => false,
            'limit' => 255,
            'attr' => [
                'class' => 'product_name',
            ],
        ]);
        $editProductRowForm->add('product_reference', TextType::class, [
            'label' => false,
            'attr' => [
                'class' => 'product_reference',
            ],
        ]);
        $editProductRowForm->add('product_supplier_reference', TextType::class, [
            'label' => false,
            'attr' => [
                'class' => 'product_supplier_reference',
            ],
        ]);
        $editProductRowForm->add('product_weight', TextType::class, [
            'label' => false,
            'attr' => [
                'class' => 'product_weight',
            ],
        ]);
        $editProductRowForm->add('reduction_percent', TextType::class, [
            'label' => false,
            'attr' => [
                'class' => 'reduction_percent',
            ],
        ]);

        $taxList = Tax::getTaxes($this->decoratedController->getContextLangId(), true);
        $taxListResult = [];
        foreach($taxList as $tax){
            $taxListResult[$tax['rate']] = $tax['rate'];
        }

        $editProductRowForm->add('tax_rate', ChoiceType::class, [
            'label' => false,
            'choices' => $taxListResult,
            'attr' => [
                'class' => 'tax_rate',
            ],
        ]);

        $list = $this->getOrderDetailList($orderId);
        $order_detail_list = [];
        foreach($list as $item){
            $order_detail_list[$item['id_order_detail']] = $item;
        }

        $order_carrier_list = $this->getOrderCarrier($orderId);

        $order = new Order($orderId);

        $order_carrier_list['tax_rate'] = $order->carrier_tax_rate;

        $address = new Address($order->id_address_delivery);
        $cart = new Cart($order->id_cart);
        $all_order_carrier = Carrier::getCarriers($this->decoratedController->getContextLangId(), true, false, false, null, Carrier::ALL_CARRIERS);
        $carrier_list = [];
        foreach($all_order_carrier as $carrier){
            $carrier_obj = new Carrier((int)$carrier['id_carrier']);
            $tax_rate = (float)$carrier_obj->getTaxesRate($address);
            $carrier_price = (float)$cart->getPackageShippingCost($carrier['id_carrier']);
            $carrier['price'] = $carrier_price;
            $carrier['price_wt'] = $carrier_price * (1 + $tax_rate / 100);
            $carrier['tax_rate'] = $tax_rate;
            $carrier_list[$carrier['id_carrier']] = $carrier;
        }

        return $this->decoratedController->render('@PrestaShop/Admin/Sell/Order/Order/view.html.twig', [
            'employeeList' => Employee::getEmployees(), // These data don't exist in original object
            'statesList' => OrderState::getOrderStates($this->decoratedController->getContextLangId()), // These data don't exist in original object
            'orderRaw' => $order, // These data don't exist in original object
            'carrierListRaw' => $carrier_list, // These data don't exist in original object
            'orderCarriersRaw' => $order_carrier_list, // These data don't exist in original object
            'orderDetailRaw' => $order_detail_list, // These data don't exist in original object
            'showContentHeader' => true,
            'enableSidebar' => true,
            'orderCurrency' => $orderCurrency,
            'meta_title' => $this->decoratedController->trans('Orders', 'Admin.Orderscustomers.Feature'),
            'help_link' => $this->decoratedController->generateSidebarLink($request->attributes->get('_legacy_controller')),
            'orderForViewing' => $orderForViewing,
            'addOrderCartRuleForm' => $addOrderCartRuleForm->createView(),
            'updateOrderStatusForm' => $updateOrderStatusForm->createView(),
            'updateOrderStatusActionBarForm' => $updateOrderStatusActionBarForm->createView(),
            'addOrderPaymentForm' => $addOrderPaymentForm->createView(),
            'changeOrderCurrencyForm' => $changeOrderCurrencyForm->createView(),
            'privateNoteForm' => $privateNoteForm ? $privateNoteForm->createView() : null,
            'updateOrderShippingForm' => $updateOrderShippingForm->createView(),
            'cancelProductForm' => $cancelProductForm->createView(),
            'invoiceManagementIsEnabled' => $orderForViewing->isInvoiceManagementIsEnabled(),
            'changeOrderAddressForm' => $changeOrderAddressForm ? $changeOrderAddressForm->createView() : null,
            'orderMessageForm' => $orderMessageForm->createView(),
            'addProductRowForm' => $addProductRowForm->createView(),
            'editProductRowForm' => $editProductRowForm->createView(),
            'backOfficeOrderButtons' => $backOfficeOrderButtons,
            'merchandiseReturnEnabled' => $merchandiseReturnEnabled,
            'priceSpecification' => $this->decoratedController->getContextLocale()->getPriceSpecification($orderCurrency->iso_code)->toArray(),
            'previousOrderId' => $orderSiblingProvider->getPreviousOrderId($orderId),
            'nextOrderId' => $orderSiblingProvider->getNextOrderId($orderId),
            'paginationNum' => $paginationNum,
            'paginationNumOptions' => $paginationNumOptions,
        ]);
    }

    private function getOrderDetailList($orderId)
    {
        return Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'order_detail` WHERE `id_order` = ' . $orderId);
    }

    private function getOrderCarrier($orderId)
    {
        return Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'order_carrier` WHERE `id_order` = ' . $orderId);
    }

    /** Proxied functions */
    public function indexAction(Request $request, OrderFilters $filters)
    {
        return $this->decoratedController->indexAction($request, $filters);
    }

    private function getOrderToolbarButtons(): array
    {
        $toolbarButtons = [];
        $toolbarButtons['add'] = [
            'href' => $this->generateUrl('admin_orders_create'),
            'desc' => $this->trans('Add new order', 'Admin.Orderscustomers.Feature'),
            'icon' => 'add_circle_outline',
        ];
        return $toolbarButtons;
    }

    public function placeAction(Request $request)
    {
        return $this->decoratedController->placeAction($request);
    }

    public function createAction(Request $request)
    {
        return $this->decoratedController->createAction($request);
    }

    public function searchAction(Request $request)
    {
        return $this->decoratedController->searchAction($request);
    }

    public function generateInvoicePdfAction($orderId)
    {
        return $this->decoratedController->generateInvoicePdfAction($orderId);
    }

    public function generateDeliverySlipPdfAction($orderId)
    {
        return $this->decoratedController->generateDeliverySlipPdfAction($orderId);
    }

    public function changeOrdersStatusAction(Request $request)
    {
        return $this->decoratedController->changeOrdersStatusAction($request);
    }

    public function exportAction(OrderFilters $filters)
    {
        return $this->decoratedController->exportAction($filters);
    }

    public function partialRefundAction(int $orderId, Request $request)
    {
        return $this->decoratedController->partialRefundAction($orderId, $request);
    }

    public function standardRefundAction(int $orderId, Request $request)
    {
        return $this->decoratedController->standardRefundAction($orderId, $request);
    }

    public function returnProductAction(int $orderId, Request $request)
    {
        return $this->decoratedController->returnProductAction($orderId, $request);
    }

    private function handleOutOfStockProduct(OrderForViewing $orderForViewing)
    {
        $isStockManagementEnabled = $this->configuration->get('PS_STOCK_MANAGEMENT');
        if (!$isStockManagementEnabled || $orderForViewing->isDelivered() || $orderForViewing->isShipped()) {
            return;
        }

        foreach ($orderForViewing->getProducts()->getProducts() as $product) {
            if ($product->getAvailableQuantity() <= 0) {
                $this->addFlash(
                    'warning',
                    $this->trans('This product is out of stock:', 'Admin.Orderscustomers.Notification') . ' ' . $product->getName()
                );
            }
        }
    }

    public function addProductAction(int $orderId, Request $request): Response
    {
        /** @var OrderForViewing $orderForViewing */
        $orderForViewing = $this->getQueryBus()->handle(new GetOrderForViewing($orderId, QuerySorting::DESC));

        $previousProducts = [];
        foreach ($orderForViewing->getProducts()->getProducts() as $orderProductForViewing) {
            $previousProducts[$orderProductForViewing->getOrderDetailId()] = $orderProductForViewing;
        }

        $invoiceId = (int) $request->get('invoice_id');
        try {
            if ($invoiceId > 0) {
                $addProductCommand = AddProductToOrderCommand::toExistingInvoice(
                    $orderId,
                    $invoiceId,
                    (int) $request->get('product_id'),
                    (int) $request->get('combination_id'),
                    $request->get('price_tax_incl'),
                    $request->get('price_tax_excl'),
                    (int) $request->get('quantity')
                );
            } else {
                $hasFreeShipping = null;
                if ($request->request->has('free_shipping')) {
                    $hasFreeShipping = (bool) filter_var($request->get('free_shipping'), FILTER_VALIDATE_BOOLEAN);
                }
                $addProductCommand = AddProductToOrderCommand::withNewInvoice(
                    $orderId,
                    (int) $request->get('product_id'),
                    (int) $request->get('combination_id'),
                    $request->get('price_tax_incl'),
                    $request->get('price_tax_excl'),
                    (int) $request->get('quantity'),
                    $hasFreeShipping
                );
            }
            $this->getCommandBus()->handle($addProductCommand);
        } catch (Exception $e) {
            return $this->json(
                ['message' => $this->getErrorMessageForException($e, $this->getErrorMessages($e))],
                Response::HTTP_BAD_REQUEST
            );
        }

        /**
         * Returning the products list view is not required since we reload the whole list
         * We keep it for now to avoid Breaking Change
         */
        /** @var OrderForViewing $orderForViewing */
        $orderForViewing = $this->getQueryBus()->handle(new GetOrderForViewing($orderId, QuerySorting::DESC));

        $updatedProducts = [];
        foreach ($orderForViewing->getProducts()->getProducts() as $orderProductForViewing) {
            $updatedProducts[$orderProductForViewing->getOrderDetailId()] = $orderProductForViewing;
        }

        $newProducts = array_diff_key($updatedProducts, $previousProducts);

        $formBuilder = $this->get('prestashop.core.form.identifiable_object.builder.cancel_product_form_builder');
        $cancelProductForm = $formBuilder->getFormFor($orderId);

        $currencyDataProvider = $this->container->get('prestashop.adapter.data_provider.currency');
        $orderCurrency = $currencyDataProvider->getCurrencyById($orderForViewing->getCurrencyId());

        $list = $this->getOrderDetailList($orderId);
        $order_detail_list = [];
        foreach($list as $item){
            $order_detail_list[$item['id_order_detail']] = $item;
        }

        $addedGridRows = '';
        foreach ($newProducts as $newProduct) {
            $addedGridRows .= $this->renderView('@PrestaShop/Admin/Sell/Order/Order/Blocks/View/product.html.twig', [
                'orderDetailRaw' => $order_detail_list, // #TODO These data don't exist in original object
                'orderForViewing' => $orderForViewing,
                'product' => $newProduct,
                'isColumnLocationDisplayed' => $newProduct->getLocation() !== '',
                'isColumnRefundedDisplayed' => $newProduct->getQuantityRefunded() > 0,
                'isAvailableQuantityDisplayed' => $this->configuration->getBoolean('PS_STOCK_MANAGEMENT'),
                'cancelProductForm' => $cancelProductForm->createView(),
                'orderCurrency' => $orderCurrency,
            ]);
        }

        return new Response($addedGridRows);
    }

    public function getProductPricesAction(int $orderId): Response
    {
        return $this->decoratedController->getProductPricesAction($orderId);
    }

    public function getInvoicesAction(int $orderId)
    {
        return $this->decoratedController->getInvoicesAction($orderId);
    }

    public function getDocumentsAction(int $orderId)
    {
        return $this->decoratedController->getDocumentsAction($orderId);
    }

    public function updateShippingAction(int $orderId, Request $request): RedirectResponse
    {
        return $this->decoratedController->updateShippingAction($orderId, $request);
    }

    public function removeCartRuleAction(int $orderId, int $orderCartRuleId): RedirectResponse
    {
        $this->getCommandBus()->handle(
            new DeleteCartRuleFromOrderCommand($orderId, $orderCartRuleId)
        );
        /** We need to change the data in the `ps_order_detail_tax` table */
        $order = new Order($orderId);
        $order->updateOrderDetailTax();
        $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));

        return $this->redirectToRoute('admin_orders_view', [
            'orderId' => $orderId,
        ]);
    }

    public function updateInvoiceNoteAction(int $orderId, int $orderInvoiceId, Request $request): RedirectResponse
    {
        return $this->decoratedController->updateInvoiceNoteAction($orderId, $orderInvoiceId, $request);
    }

    public function updateProductAction(int $orderId, int $orderDetailId, Request $request): Response
    {
        return $this->decoratedController->updateProductAction($orderId, $orderDetailId, $request);
    }

    public function addCartRuleAction(int $orderId, Request $request): RedirectResponse
    {
        $addOrderCartRuleForm = $this->createForm(AddOrderCartRuleType::class, [], [
            'order_id' => $orderId,
        ]);
        $addOrderCartRuleForm->handleRequest($request);

        if ($addOrderCartRuleForm->isSubmitted()) {
            if ($addOrderCartRuleForm->isValid()) {
                $data = $addOrderCartRuleForm->getData();

                try {
                    $this->getCommandBus()->handle(
                        new AddCartRuleToOrderCommand(
                            $orderId,
                            $data['name'],
                            $data['type'],
                            $data['value'] ?? null,
                            empty($data['invoice_id']) ? null : (int) $data['invoice_id']
                        )
                    );
                    /** We need to change the data in the `ps_order_detail_tax` table */
                    $order = new Order($orderId);
                    $order->updateOrderDetailTax();

                    $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));
                } catch (Exception $e) {
                    $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));
                }
            } else {
                foreach ($addOrderCartRuleForm->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->redirectToRoute('admin_orders_view', [
            'orderId' => $orderId,
        ]);
    }

    public function updateStatusAction(int $orderId, Request $request): RedirectResponse
    {
        return $this->decoratedController->updateStatusAction($orderId, $request);
    }

    public function updateStatusFromListAction(int $orderId, Request $request): RedirectResponse
    {
        return $this->decoratedController->updateStatusFromListAction($orderId, $request);
    }

    public function addPaymentAction(int $orderId, Request $request): RedirectResponse
    {
        return $this->decoratedController->addPaymentAction($orderId, $request);
    }

    public function previewAction(int $orderId): JsonResponse
    {
        return $this->decoratedController->previewAction($orderId);
    }

    public function duplicateOrderCartAction(int $orderId)
    {
        return $this->decoratedController->duplicateOrderCartAction($orderId);
    }

    public function sendMessageAction(Request $request, int $orderId): Response
    {
        return $this->decoratedController->sendMessageAction($request, $orderId);
    }

    public function changeCustomerAddressAction(Request $request): RedirectResponse
    {
        return $this->decoratedController->changeCustomerAddressAction($request);
    }

    public function changeCurrencyAction(int $orderId, Request $request): RedirectResponse
    {
        return $this->decoratedController->changeCurrencyAction($orderId, $request);
    }

    public function resendEmailAction(int $orderId, int $orderStatusId, int $orderHistoryId): RedirectResponse
    {
        return $this->decoratedController->resendEmailAction($orderId, $orderStatusId, $orderHistoryId);
    }

    public function deleteProductAction(int $orderId, int $orderDetailId): JsonResponse
    {
        return $this->decoratedController->deleteProductAction($orderId, $orderDetailId);
    }

    public function getDiscountsAction(int $orderId): Response
    {
        return $this->decoratedController->getDiscountsAction($orderId);
    }

    public function getPricesAction(int $orderId): JsonResponse
    {
        return $this->decoratedController->getPricesAction($orderId);
    }

    public function getPaymentsAction(int $orderId): Response
    {
        return $this->decoratedController->getPaymentsAction($orderId);
    }

    public function getProductsListAction(int $orderId): Response
    {
        return $this->decoratedController->getProductsListAction($orderId);
    }

    public function generateInvoiceAction(int $orderId): RedirectResponse
    {
        return $this->decoratedController->generateInvoiceAction($orderId);
    }

    public function sendProcessOrderEmailAction(Request $request): JsonResponse
    {
        return $this->decoratedController->sendProcessOrderEmailAction($request);
    }

    public function cancellationAction(int $orderId, Request $request)
    {
        return $this->decoratedController->cancellationAction($orderId, $request);
    }

    public function configureProductPaginationAction(Request $request): JsonResponse
    {
        return $this->decoratedController->configureProductPaginationAction($request);
    }

    public function displayCustomizationImageAction(int $orderId, string $name, string $value)
    {

        if (version_compare(_PS_VERSION_, '1.7.7.7', '>=')) {
            return $this->decoratedController->displayCustomizationImageAction($orderId, $name);
        } else {
            return $this->decoratedController->displayCustomizationImageAction($orderId, $name, $value);
        }
    }

    public function searchProductsAction(Request $request): JsonResponse
    {
        return $this->decoratedController->searchProductsAction($request);
    }

    private function handleOrderStatusUpdate(int $orderId, int $orderStatusId): void
    {
        try {
            $this->getCommandBus()->handle(
                new UpdateOrderStatusCommand(
                    $orderId,
                    $orderStatusId
                )
            );
            $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));
        } catch (ChangeOrderStatusException $e) {
            $this->handleChangeOrderStatusException($e);
        } catch (Exception $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));
        }
    }

    private function getErrorMessages(Exception $e)
    {
        $refundableQuantity = 0;
        if ($e instanceof InvalidCancelProductException) {
            $refundableQuantity = $e->getRefundableQuantity();
        }
        $orderInvoiceNumber = '#unknown';
        if ($e instanceof DuplicateProductInOrderInvoiceException) {
            $orderInvoiceNumber = $e->getOrderInvoiceNumber();
        }

        return [
            CannotEditDeliveredOrderProductException::class => $this->trans('You cannot edit the cart once the order delivered.', 'Admin.Orderscustomers.Notification'),
            OrderNotFoundException::class => $e instanceof OrderNotFoundException ?
                $this->trans(
                    'Order #%d cannot be loaded.',
                    'Admin.Orderscustomers.Notification',
                    ['#%d' => $e->getOrderId()->getValue()]
                ) : '',
            OrderEmailSendException::class => $this->trans(
                'An error occurred while sending the e-mail to the customer.',
                'Admin.Orderscustomers.Notification'
            ),
            OrderException::class => $this->trans(
                $e->getMessage(),
                'Admin.Orderscustomers.Notification'
            ),
            InvalidAmountException::class => $this->trans(
                'Only numbers and decimal points (".") are allowed in the amount fields, e.g. 10.50 or 1050.',
                'Admin.Orderscustomers.Notification'
            ),
            InvalidCartRuleDiscountValueException::class => [
                InvalidCartRuleDiscountValueException::INVALID_MIN_PERCENT => $this->trans(
                    'Percent value must be greater than 0.',
                    'Admin.Orderscustomers.Notification'
                ),
                InvalidCartRuleDiscountValueException::INVALID_MAX_PERCENT => $this->trans(
                    'Percent value cannot exceed 100.',
                    'Admin.Orderscustomers.Notification'
                ),
                InvalidCartRuleDiscountValueException::INVALID_MIN_AMOUNT => $this->trans(
                    'Amount value must be greater than 0.',
                    'Admin.Orderscustomers.Notification'
                ),
                InvalidCartRuleDiscountValueException::INVALID_MAX_AMOUNT => $this->trans(
                    'Discount value cannot exceed the total price of this order.',
                    'Admin.Orderscustomers.Notification'
                ),
                InvalidCartRuleDiscountValueException::INVALID_FREE_SHIPPING => $this->trans(
                    'Shipping discount value cannot exceed the total price of this order.',
                    'Admin.Orderscustomers.Notification'
                ),
            ],
            InvalidCancelProductException::class => [
                InvalidCancelProductException::INVALID_QUANTITY => $this->trans(
                    'Positive product quantity is required.',
                    'Admin.Notifications.Error'
                ),
                InvalidCancelProductException::QUANTITY_TOO_HIGH => $this->trans(
                    'Please enter a maximum quantity of [1].',
                    'Admin.Orderscustomers.Notification',
                    ['[1]' => $refundableQuantity]
                ),
                InvalidCancelProductException::NO_REFUNDS => $this->trans(
                    'Please select at least one product.',
                    'Admin.Orderscustomers.Notification'
                ),
                InvalidCancelProductException::INVALID_AMOUNT => $this->trans(
                    'Please enter a positive amount.',
                    'Admin.Orderscustomers.Notification'
                ),
                InvalidCancelProductException::NO_GENERATION => $this->trans(
                    'Please generate at least one credit slip or voucher.',
                    'Admin.Orderscustomers.Notification'
                ),
            ],
            InvalidModuleException::class => $this->trans(
                'You must choose a payment module to create the order.',
                'Admin.Orderscustomers.Notification'
            ),
            ProductOutOfStockException::class => $this->trans(
                'There are not enough products in stock.',
                'Admin.Catalog.Notification'
            ),
            NegativePaymentAmountException::class => $this->trans(
                'Invalid value: the payment must be a positive amount.',
                'Admin.Notifications.Error'
            ),
            InvalidOrderStateException::class => [
                InvalidOrderStateException::ALREADY_PAID => $this->trans(
                    'Invalid action: this order has already been paid.',
                    'Admin.Notifications.Error'
                ),
                InvalidOrderStateException::DELIVERY_NOT_FOUND => $this->trans(
                    'Invalid action: this order has not been delivered.',
                    'Admin.Notifications.Error'
                ),
                InvalidOrderStateException::UNEXPECTED_DELIVERY => $this->trans(
                    'Invalid action: this order has already been delivered.',
                    'Admin.Notifications.Error'
                ),
                InvalidOrderStateException::NOT_PAID => $this->trans(
                    'Invalid action: this order has not been paid.',
                    'Admin.Notifications.Error'
                ),
                InvalidOrderStateException::INVALID_ID => $this->trans(
                    'You must choose an order status to create the order.',
                    'Admin.Orderscustomers.Notification'
                ),
            ],

            OrderConstraintException::class => [
                OrderConstraintException::INVALID_CUSTOMER_MESSAGE => $this->trans(
                    'The order message given is invalid.',
                    'Admin.Orderscustomers.Notification'
                ),
            ],
            InvalidProductQuantityException::class => $this->trans(
                'Positive product quantity is required.',
                'Admin.Notifications.Error'
            ),
            DuplicateProductInOrderException::class => $this->trans(
                'This product is already in your order, please edit the quantity instead.',
                'Admin.Notifications.Error'
            ),
            DuplicateProductInOrderInvoiceException::class => $this->trans(
                'This product is already in the invoice [1], please edit the quantity instead.',
                'Admin.Notifications.Error',
                ['[1]' => $orderInvoiceNumber]
            ),
            CannotFindProductInOrderException::class => $this->trans(
                'You cannot edit the price of a product that no longer exists in your catalog.',
                'Admin.Notifications.Error'
            ),
        ];
    }

    private function getPaymentErrorMessages(Exception $e)
    {
        return array_merge($this->getErrorMessages($e), [
            InvalidArgumentException::class => $this->trans(
                'Only numbers and decimal points (".") are allowed in the amount fields of the payment block, e.g. 10.50 or 1050.',
                'Admin.Orderscustomers.Notification'
            ),
        ]);
    }

    private function handleChangeOrderStatusException(ChangeOrderStatusException $e)
    {
        $orderIds = array_merge(
            $e->getOrdersWithFailedToUpdateStatus(),
            $e->getOrdersWithFailedToSendEmail()
        );

        /** @var OrderId $orderId */
        foreach ($orderIds as $orderId) {
            $this->addFlash(
                'error',
                $this->trans(
                    'An error occurred while changing the status for order #%d, or we were unable to send an email to the customer.',
                    'Admin.Orderscustomers.Notification',
                    ['#%d' => $orderId->getValue()]
                )
            );
        }

        foreach ($e->getOrdersWithAssignedStatus() as $orderId) {
            $this->addFlash(
                'error',
                $this->trans(
                    'Order #%d has already been assigned this status.',
                    'Admin.Orderscustomers.Notification',
                    ['#%d' => $orderId->getValue()]
                )
            );
        }
    }

    private function getCustomerMessageErrorMapping(Exception $exception): array
    {
        return [
            OrderNotFoundException::class => $exception instanceof OrderNotFoundException ?
                $this->trans(
                    'Order #%d cannot be loaded.',
                    'Admin.Orderscustomers.Notification',
                    ['#%d' => $exception->getOrderId()->getValue()]
                ) : '',
            CustomerMessageConstraintException::class => [
                CustomerMessageConstraintException::MISSING_MESSAGE => $this->trans(
                    'The %s field is not valid',
                    'Admin.Notifications.Error',
                    [
                        sprintf('"%s"', $this->trans('Message', 'Admin.Global')),
                    ]
                ),
                CustomerMessageConstraintException::INVALID_MESSAGE => $this->trans(
                    'The %s field is not valid',
                    'Admin.Notifications.Error',
                    [
                        sprintf('"%s"', $this->trans('Message', 'Admin.Global')),
                    ]
                ),
            ],
        ];
    }
    /** END Proxied functions */

    /** Only since 1.7.8.0 */
    public function getShippingAction(int $orderId)
    {
        return $this->decoratedController->getShippingAction($orderId);
    }
}
