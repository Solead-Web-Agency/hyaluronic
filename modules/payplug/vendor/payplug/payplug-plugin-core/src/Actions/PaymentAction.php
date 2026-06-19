<?php

declare(strict_types=1);

namespace PayplugPluginCore\Actions;

use PayplugPluginCore\Models\Entities\PaymentInputDTO;
use PayplugPluginCore\Models\Entities\PaymentOutputDTO;
use PayplugPluginCore\Utilities\Services\Api;
use PayplugPluginCore\Utilities\Traits\DependenciesLoader;

class PaymentAction
{
    use DependenciesLoader;

    public function __construct()
    {
        $this->allowed_gateways = ['payment'];
        $this->allowed_services = ['api'];
    }

    /**
     * @param PaymentInputDTO $payment_inputDTO
     *
     * @return ?PaymentOutputDTO
     *@throws \Exception
     *
     */
    public function createAction(PaymentInputDTO $payment_inputDTO): ?PaymentOutputDTO
    {
        // todo: add a validator to check if the given paymentDTO is usable

        if (null === $payment_inputDTO->getPaymentMethod()) {
            throw new \Exception('Invalid parameter, payment method is required.');
        }

        // get payment method from given arg
        $payment_method = $this
            ->get_payment_gateway()
            ->load($payment_inputDTO->getPaymentMethod());

        // get payment tab appropriate or return error if need to
        $formated_attributes = $payment_method->formatPaymentAttributes($payment_inputDTO);

        // load api service with given then return the fallback
        /** @var Api $api_service */
        $api_service = $this->get_service('api');
        $api = $api_service->load((string) $payment_inputDTO->getApiBearer());
        $resource = $api->createPaymentResource($formated_attributes);

        return PaymentOutputDTO::create($resource);
    }

    /**
     * @description
     */
    public function abortAction(): void
    {
    }

    /**
     * @description
     */
    public function captureAction(): void
    {
    }

    /**
     * @description
     */
    public function refundAction(): void
    {
    }
}
