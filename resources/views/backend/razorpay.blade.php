<!DOCTYPE html>
<html>
<head>
    <title>Order Payment</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <form id="payment-form" action="{{ url('api/order-payment') }}" method="POST">
        @csrf
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var amount = @json($data['amount']);
            if (amount) {
                var options = {
                    "key": "{{env('RAZORPAY_KEY')}}",
                    "amount": amount,
                    "currency": "INR",
                    "order_id": "{{ $data['order_id'] }}",
                    // "name": "Success Beautician Academy",
                    // "description": "Success Beautician Academy Course Register",
                    "theme": {
                        "color": "rgb(35,113,236)"
                    },
                    "handler": function (response) {
                        console.log(response);
                        var form = document.getElementById('payment-form');
                        var hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'razorpay_payment_id';
                        hiddenInput.value = response.razorpay_payment_id;
                        form.appendChild(hiddenInput);
                        var orderIdInput = document.createElement('input');
                        orderIdInput.type = 'hidden';
                        orderIdInput.name = 'razorpay_order_id';
                        orderIdInput.value = response.razorpay_order_id;
                        form.appendChild(orderIdInput);
                        var signatureInput = document.createElement('input');
                        signatureInput.type = 'hidden';
                        signatureInput.name = 'razorpay_signature';
                        signatureInput.value = response.razorpay_signature;
                        form.appendChild(signatureInput);
                        form.submit();
                    }
                };

                var rzp = new Razorpay(options);
                rzp.open();
            }
        });
    </script>
</body>
</html>
