<?php

namespace Acelle\Library;

use Acelle\Model\Subscription;
use Acelle\Library\TransactionResult;
use Acelle\Model\SubscriptionLog;
use Acelle\Model\Invoice;

class SubscriptionManager
{
    // Look for expired subscriptions and end it
    public function endExpiredSubscriptions()
    {
        $subscriptions = Subscription::active()->get();

        foreach ($subscriptions as $subscription) {
            $subscription->endIfExpired();
        }
    }

    // Look for expiring subscription and generate renew invoices
    public function createRenewInvoices()
    {
        $subscriptions = Subscription::active()->get();

        foreach ($subscriptions as $subscription) {
            $subscription->checkAndCreateRenewInvoice();
        }
    }

    // Auto pay renew invoices
    public function autoChargeRenewInvoices()
    {
        $renewInvoices = \Acelle\Model\InvoiceRenewSubscription::renew()->unpaid()->get();

        foreach ($renewInvoices as $invoice) {
            $subscription = $invoice->mapType()->subscription;

            $customer = $subscription->customer;

            applog('invoice')->info(sprintf("START CHECKING customer %s for invoice #%s AMOUNT: %s, TYPE: %s", $customer->name, $invoice->uid, $invoice->formattedTotal(), $invoice->type));

            // not reach due date
            if (!$subscription->isBillingPeriod()) {
                $billingDate = $subscription->getAutoBillingDate();
                $diffs = $billingDate->diffForHumans(now());

                applog('invoice')->info(sprintf("+ NOT DUE YET! Invoice of customer '%s' #%s AMOUNT: %s, TYPE: %s is not due for billing until %s, i.e. %s", $customer->name, $invoice->uid, $invoice->formattedTotal(), $invoice->type, $billingDate, $diffs));
                // do nothing
                continue;
            }

            // check if customer can auto charge
            if (!$customer->preferredPaymentGatewayCanAutoCharge()) {
                applog('invoice')->info(sprintf("+ NO AUTO BILLING! Preferred payment method of customer '%s' does not support auto billing, skipped #%s AMOUNT: %s, TYPE: %s", $customer->name, $invoice->uid, $invoice->formattedTotal(), $invoice->type));

                continue;
            }

            /**
             *  What if $customer->getPreferredPaymentGateway() is null?
             *  So, simply do not support bypass ZERO invoice
             *
            if ($invoice->total() == 0) {
                // Trường hợp invoice total = 0 thì pay nothing và set done luôn cho renew invoice
                $invoice->checkout($customer->getPreferredPaymentGateway(), function ($invoice) {
                    return new TransactionResult(TransactionResult::RESULT_DONE);
                });

                continue;
            }
            */

            // Bypass payment when no_payment_required_when_free
            if ($invoice->no_payment_required_when_free && $invoice->isFree()) {
                applog('invoice')->info(sprintf("+ BYPASS! Bypass this invoice for customer '%s' #%s AMOUNT: %s, TYPE: %s", $customer->name, $invoice->uid, $invoice->formattedTotal(), $invoice->type));

                $invoice->bypassPayment();
                continue;
            }

            // auto charge
            $customer->getPreferredPaymentGateway()->autoCharge($invoice);
        }
    }

    public function log($subscription, $type, $invoice_uid = null, $metadata = [])
    {
        $log = $subscription->subscriptionLogs()->create([
            'type' => $type,
            'invoice_uid' => $invoice_uid,
        ]);

        if (isset($metadata)) {
            $log->updateData($metadata);
        }

        return $log;
    }
}
