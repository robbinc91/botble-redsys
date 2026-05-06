<div class="card">
    <div class="card-body">
        <p><strong>{{ trans('plugins/redsys::redsys.messages.payment_reference') }}:</strong> {{ $payment->charge_id }}</p>
        <p class="mb-0 text-muted">{{ trans('plugins/redsys::redsys.messages.paid_via_redsys') }}</p>
    </div>
</div>
