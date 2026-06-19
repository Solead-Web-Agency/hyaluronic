<?php
/**
 * A Route Prestashop Extension that adds secure shipping
 * protection to your orders
 *
 * Php version 7.0^
 *
 * @author    Route Development Team <dev@routeapp.io>
 * @copyright 2019 Route App Inc. Copyright (c) https://www.routeapp.io/
 * @license   https://www.routeapp.io/merchant-terms-of-use  Proprietary License
 */

class RouteHelper
{
    public static function buildRouteOrder($order_id)
    {
        $order = new Order($order_id);
        $order_details = OrderDetail::getList($order_id);
        $customer = new Customer($order->id_customer);
        $address = new Address($order->id_address_delivery);
        $state = new State($address->id_state);
        $country = new Country($address->id_country);
        $lineItems = [];

        foreach ($order_details as $item) {
            $obj = new stdClass();
            $obj->source_product_id = $item['product_id'];
            $obj->source_id = (string) $item['id_order_detail'];
            $obj->sku = $item['product_reference'];
            $obj->name = $item['product_name'];
            $obj->price = $item['product_price'];
            $obj->quantity = $item['product_quantity'];
            $obj->image_url = self::getImageUrl($obj->source_product_id);

            array_push($lineItems, $obj);
        }

        $routeFee = RouteappFee::getByOrderId($order_id);
        $hasInsurance = !$routeFee ? false : true; // return is false or float
        $paidToInsure = $hasInsurance ? $routeFee->fee_amount : 0.0;

        $sourceCreatedOn = self::getDateTimeInUTC($order->date_add);
        $sourceUpdatedOn = self::getDateTimeInUTC($order->date_upd);

        return [
            'source_order_id' => $order->reference,
            'source_order_number' => $order->id,
            'subtotal' => $order->total_paid_tax_incl,
            'taxes' => $order->total_paid_tax_incl - $order->total_paid_tax_excl,
            'currency' => Currency::getDefaultCurrency()->iso_code,
            'insurance_selected' => $hasInsurance,
            'customer_details' => [
                'first_name' => $customer->firstname,
                'last_name' => $customer->lastname,
                'email' => $customer->email,
            ],
            'shipping_details' => [
                'first_name' => $address->firstname,
                'last_name' => $address->lastname,
                'street_address1' => $address->address1,
                'street_address2' => $address->address2,
                'city' => $address->city,
                'province' => $state->name,
                'zip' => $address->postcode,
                'country_code' => $country->iso_code,
            ],
            'line_items' => $lineItems,
            'paid_to_insure' => $paidToInsure,
            'source_created_on' => $sourceCreatedOn,
            'source_updated_on' => $sourceUpdatedOn,
        ];
    }

    public static function getDateTimeInUTC($dateTime, $format = 'Y-m-d H:i:s')
    {
        $storeTimezone = Configuration::get('PS_TIMEZONE');
        $customDate = DateTime::createFromFormat(
            $format,
            $dateTime,
            new DateTimeZone($storeTimezone)
        );
        $utcDate = clone $customDate;
        $utcDate->setTimeZone(new DateTimeZone('UTC'));

        return $utcDate->format($format);
    }

    public static function buildRouteShipment($order, $carrier)
    {
        $order_details = OrderDetail::getList($order->id);
        $sourceProductIds = [];
        $lineItems = [];
        foreach ($order_details as $item) {
            array_push($sourceProductIds, $item['product_id']);
            $lineItems[] = [
                'source_id' => (string) $item['id_order_detail'],
                'quantity' => (int) $item['product_quantity'],
            ];
        }

        return [
            'tracking_number' => $order->shipping_number,
            'source_order_id' => $order->reference,
            'source_product_ids' => $sourceProductIds,
            'courier_id' => $carrier->name,
            'line_items' => $lineItems,
        ];
    }

    private static function getImageUrl($product_id)
    {
        $cover = Image::getCover($product_id);
        $image = new Image($cover['id_image']);

        return _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath() . '.jpg';
    }
}
