<?php

namespace DigiTickets\Stripe;

use DigiTickets\Stripe\Messages\CompletePurchaseRequest;
use DigiTickets\Stripe\Messages\PurchaseRequest;
use DigiTickets\Stripe\Messages\RefundRequest;
use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Message\RequestInterface;

/**
 * This gateway uses checkout redirect version of Stripe payment
 * which has integrated support for GooglePay and ApplePay.
 * https://stripe.com/payments/checkout
 * https://stripe.com/docs/payments/checkout (with test link "Preview checkout")
 * https://stripe.com/docs/payments/checkout/accept-a-payment
 * https://stripe.com/docs/api/checkout/sessions/object
 *
 *
 *
 * // Initialize :
 * $gateway = \Omnipay\Omnipay::create('\DigiTickets\Stripe\CheckoutGateway');
 * $gateway->initialize([
 *     'apiKey' => $secretKey,
 *     'public' => $publicKey,
 * ]);
 *
 * // placeholder {CHECKOUT_SESSION_ID} is on return redirect filled with session_id
 * // https://stripe.com/docs/payments/checkout/accept-a-payment#create-checkout-session
 * $successUrl = 'https://server/success-url?session_id={CHECKOUT_SESSION_ID}'
 * $errorUrl   = 'https://server/error-url?session_id={CHECKOUT_SESSION_ID}'
 *
 * $intentData = [
 *     'currency'             => $currency,
 *     'transactionId'        => $orderId,
 *     'customer_email'       => $email, //optional
 *     'returnUrl'            => $successUrl,
 *     'cancelUrl'            => $errorUrl,
 *     'payment_method_types' => ['card'], // optional
 *     'metadata'             => [ // optional
 *         //any key value data, displayed in dashboard
 *     ],
 *     'items'             => [
 *         [
 *             'name'        => $itemName,
 *             'description' => $itemDescription, // optional
 *             'price'       => $itemPriceForOne,
 *             'quantity'    => $itemQuantity,
 *         ],
 *     ],
 * ];
 * // \DigiTickets\Stripe\Messages\PurchaseResponse $response
 * $response               = $gateway->purchase($intentData)->send();
 * $sessionId = $response->getSessionID();
 *
 *
 *
 * // Redirect :
 *
 * // Only javascript redirect is supported, because redirect to stripe is created by stripe js lib
 * // https://stripe.com/docs/js/checkout/redirect_to_checkout
 * echo "<script src='https://js.stripe.com/v3/'></script>";
 *
 * if ($sessionId) {
 *     echo "<script>
 *     var stripe = Stripe('" . $publicKey . "');
 *     stripe.redirectToCheckout({
 *     sessionId: '" . $sessionId . "'
 *     }).then(function (result) {
 *     });
 *     </script>";
 * }
 *
 *
 *
 * // Handle cancelUrl :
 * // Stripe does not give different result on cancel or not complete.
 * // so the only way you know, that user canceled, is if session_id is in return url.
 * // Checking state is the same as in check successUrl
 *
 *
 * // Handle returnUrl - success :
 *
 * $transactionRef = (new \DigiTickets\Stripe\Lib\ComplexTransactionRef($sessionId))->asJson();
 * // \DigiTickets\Stripe\Messages\CompletePurchaseResponse $completePurchaseResponse
 * $completePurchaseResponse = $this->gateway->completePurchase(['transactionReference' => $transactionRef,])->send();
 *
 * //check :
 * $completePurchaseResponse->isSuccessful();
 * $completePurchaseResponse->getMessage();
 *
 * // Redirects create a situation, that you possibly cat have multiple running attempts to pay
 * // and/or redirects can fail, even after success
 * // I suggest, that you store all session_id from all attempts , related to transactionId/orderId
 * // and when displaying page to pay, check them all on stripe api.
 * // User could already have payed.
 *
 *
 *
 *
 *
 * @method NotificationInterface acceptNotification(array $options = array()) (Optional method)
 *         Receive and handle an instant payment notification (IPN)
 * @method RequestInterface authorize(array $options = array())               (Optional method)
 *         Authorize an amount on the customers card
 * @method RequestInterface completeAuthorize(array $options = array())       (Optional method)
 *         Handle return from off-site gateways after authorization
 * @method RequestInterface capture(array $options = array())                 (Optional method)
 *         Capture an amount you have previously authorized
 * @method RequestInterface fetchTransaction(array $options = [])             (Optional method)
 *         Fetches transaction information
 * @method RequestInterface void(array $options = array())                    (Optional method)
 *         Generally can only be called up to 24 hours after submitting a transaction
 * @method RequestInterface createCard(array $options = array())              (Optional method)
 *         The returned response object includes a cardReference, which can be used for future transactions
 * @method RequestInterface updateCard(array $options = array())              (Optional method)
 *         Update a stored card
 * @method RequestInterface deleteCard(array $options = array())              (Optional method)
 *         Delete a stored card
 */
class CheckoutGateway extends AbstractGateway
{
    public function getDefaultParameters()
    {
        return array(
            'apiKey' => '',
            'public' => '',
        );
    }
    /**
     * Get the gateway API Key (the "secret key").
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->getParameter('apiKey');
    }

    /**
     * Set the gateway API Key.
     *
     * @param string $value
     * @return AbstractGateway provides a fluent interface.
     */
    public function setApiKey($value): AbstractGateway
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * Get the gateway public Key (the "publishable key").
     *
     * @return string
     */
    public function getPublic(): string
    {
        return $this->getParameter('public');
    }

    /**
     * Set the gateway public Key.
     *
     * @param string $value
     * @return AbstractGateway provides a fluent interface.
     */
    public function setPublic($value): AbstractGateway
    {
        return $this->setParameter('public', $value);
    }

    public function getName()
    {
        return 'Stripe (Checkout)';
    }

    public function purchase(array $parameters = []): RequestInterface
    {
        return $this->createRequest(PurchaseRequest::class, $parameters);
    }

    public function completePurchase(array $parameters = []): RequestInterface
    {
        return $this->createRequest(CompletePurchaseRequest::class, $parameters);
    }

    public function refund(array $parameters = []): RequestInterface
    {
        return $this->createRequest(RefundRequest::class, $parameters);
    }


    public function __call($name, $arguments)
    {
        // Existence of these methods omnipay tests with method_exists().
        // they are optional, but PhpStorm shows error if they are not at least in @method docblock

        // Not implementing method \Omnipay\Common\Message\NotificationInterface acceptNotification(array $options = array())
        // Not implementing method \Omnipay\Common\Message\RequestInterface authorize(array $options = array())
        // Not implementing method \Omnipay\Common\Message\RequestInterface completeAuthorize(array $options = array())
        // Not implementing method \Omnipay\Common\Message\RequestInterface capture(array $options = array())
        // Not implementing method \Omnipay\Common\Message\RequestInterface fetchTransaction(array $options = [])
        // Not implementing method \Omnipay\Common\Message\RequestInterface void(array $options = array())
        // Not implementing method \Omnipay\Common\Message\RequestInterface createCard(array $options = array())
        // Not implementing method \Omnipay\Common\Message\RequestInterface updateCard(array $options = array())
        // Not implementing method \Omnipay\Common\Message\RequestInterface deleteCard(array $options = array())
    }

}
