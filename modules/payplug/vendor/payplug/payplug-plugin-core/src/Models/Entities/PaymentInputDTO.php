<?php

declare(strict_types=1);

namespace PayplugPluginCore\Models\Entities;

use Exception;

class PaymentInputDTO
{
    /**
     * @var string|null
     */
    public $api_bearer;

    /**
     * @var string|null
     */
    public $payment_method;

    /**
     * @var int|null
     */
    public $amount;

    /**
     * @var string|null
     */
    public $currency_iso_code;

    /** @var array<string, mixed>|null */
    public $customer; // ['billing' => [...], 'shipping' => [...], 'identifier' => ...]

    /** @var array<string, string>|null */
    public $urls; // ['return' => ..., 'cancel' => ..., 'notification' => ...]

    /** @var array<string, mixed>|null */
    public $metadata;

    /** @var array<string, mixed>|null */
    public $context;

    /**
     * @var array<string, array{type: string, required: bool}>
     */
    private $definitions = [
        'api_bearer'       => ['type' => 'string', 'required' => true],
        'payment_method'   => ['type' => 'string', 'required' => true],
        'amount'           => ['type' => 'int',    'required' => true],
        'currency_iso_code' => ['type' => 'string', 'required' => true],
        'customer'         => ['type' => 'array',  'required' => true],
        'urls'             => ['type' => 'array',  'required' => true],
        'metadata'         => ['type' => 'array',  'required' => false],
        'context'          => ['type' => 'array',  'required' => false],
    ];

    /**
     * @param array<string, mixed> $props
     * @return $this|self|null
     * @throws Exception
     */
    public function hydrate(array $props): ?self
    {
        // todo: move this check in validators
        foreach ($this->getDefinitions() as $key => $field) {
            if (!$field['required']) {
                continue;
            }

            if (!\array_key_exists($key, $props) || null === $props[$key]) {
                $this->resetProperties();
                throw new Exception('PaymentInputDTO can\'t be hydrated, required field is invalid.');
            }
        }

        $this->setApiBearer((string) $props['api_bearer']);
        $this->setPaymentMethod((string) $props['payment_method']);
        $this->setAmount((int) $props['amount']);
        $this->setCurrencyIsoCode((string) $props['currency_iso_code']);
        $this->setCustomer((array) $props['customer']);
        $this->setUrls((array) $props['urls']);
        $this->setMetadata((array) $props['metadata']);
        $this->setContext((array) $props['context']);

        return $this;
    }

    private function resetProperties(): void
    {
        $this->api_bearer        = null;
        $this->payment_method    = null;
        $this->amount            = null;
        $this->currency_iso_code = null;
        $this->customer          = null;
        $this->urls              = null;
        $this->metadata          = null;
        $this->context           = null;
    }

    // Getters

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getApiBearer(): ?string
    {
        return $this->api_bearer;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    public function getCurrencyIsoCode(): ?string
    {
        return $this->currency_iso_code;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCustomer(): ?array
    {
        return $this->customer;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->payment_method;
    }

    /**
     * @return array<string, string>|null
     */
    public function getUrls(): ?array
    {
        return $this->urls;
    }

    /**
     * @return array<string, array{type: string, required: bool}>
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    // Setters

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function setApiBearer(string $api_bearer): void
    {
        $this->api_bearer = $api_bearer;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function setCurrencyIsoCode(string $currency_iso_code): void
    {
        $this->currency_iso_code = $currency_iso_code;
    }

    /**
     * @param array<string, mixed> $customer
     */
    public function setCustomer(array $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function setPaymentMethod(string $payment_method): void
    {
        $this->payment_method = $payment_method;
    }

    /**
     * @param array<string, string> $urls
     */
    public function setUrls(array $urls): void
    {
        $this->urls = $urls;
    }

    /**
     * @param array<string, mixed> $props
     * @return self|null
     */
    public static function create(array $props): ?self
    {
        return (new self())->hydrate($props);
    }
}
