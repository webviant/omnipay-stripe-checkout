<?php

namespace DigiTickets\Stripe\Messages;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Item;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class PurchaseRequest extends AbstractCheckoutRequest
{
    private function nullIfEmpty(string $value = null)
    {
        return empty($value) ? null : $value;
    }

    /**
     * {@inheritDoc}
     * @throws InvalidRequestException
     */
    public function getData()
    {
        // Just validate the parameters.
        $this->validate('apiKey', 'transactionId', 'returnUrl', 'cancelUrl');
        $items   = [];
        $itemBag = $this->getItems();
        if ($itemBag !== null) {
            $items = $itemBag->all();
        }
        // Initiate the session.
        // Unfortunately (and very, very annoyingly), the API does not allow negative- or zero value items in the
        // cart, so we have to filter them out (and re-index them) before we build the line items array.
        // Beware because the amount the customer pays is the sum of the values of the remaining items, so if you
        // supply negative-valued items, they will NOT be deducted from the payment amount.
        return [
            'client_reference_id'  => $this->getTransactionId(),
            'customer_email'       => $this->nullIfEmpty($this->getCustomerEmail()),
            'payment_method_types' => $this->getPaymentMethodTypes(),
            'line_items'           => array_map(
                function (Item $item) {
                    return [
                        'name'        => $item->getName(),
                        'description' => $this->nullIfEmpty($item->getDescription()),
                        'amount'      => (int)(100 * $item->getPrice()), // @TODO: The multiplier depends on the currency
                        'currency'    => $this->getCurrency(),
                        'quantity'    => $item->getQuantity(),
                    ];
                },
                array_values(
                    array_filter(
                        $items,
                        function (Item $item) {
                            return $item->getPrice() > 0;
                        }
                    )
                )
            ),
            'success_url'          => $this->getReturnUrl(),
            'cancel_url'           => $this->getCancelUrl(),
            'metadata'             => $this->getMetadata(),
        ];
    }

    public function getMetadata()
    {
        return $this->getParameter('metadata');
    }

    public function setMetadata($value)
    {
        return $this->setParameter('metadata', $value);
    }

    public function getCustomerEmail()
    {
        return $this->getParameter('customer_email');
    }

    public function setCustomerEmail($value)
    {
        return $this->setParameter('customer_email', $value);
    }

    public function getPaymentMethodTypes()
    {
        $paymentMethodTypes = $this->getParameter('payment_method_types');
        if (empty($paymentMethodTypes) || !is_array($paymentMethodTypes)) {
            $paymentMethodTypes = ['card'];
        }
        return $paymentMethodTypes;
    }

    public function setPaymentMethodTypes($value)
    {
        return $this->setParameter('payment_method_types', $value);
    }


    /**
     * {@inheritDoc}
     * @throws ApiErrorException
     */
    public function sendData($data)
    {
        // We use Stripe's SDK to initialise a (Stripe) session. The session gets passed through the process and is
        // used to identify this transaction.
        Stripe::setApiKey($this->getApiKey());

        $session = Session::create($data);

        return $this->response = new PurchaseResponse($this, ['session' => $session]);
    }
}
