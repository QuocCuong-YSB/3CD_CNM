import React, { useState } from 'react';
import { CardElement, useStripe, useElements } from '@stripe/react-stripe-js';
import { toast } from 'react-toastify';

const StripePaymentForm = ({ totalAmount, onPaymentSuccess, isLoading, setIsLoading, formData }) => {
    const stripe = useStripe();
    const elements = useElements();

    const handleSubmit = async (event) => {
        event.preventDefault();

        if (!stripe || !elements) return;

        if (!formData.name || !formData.address || !formData.phone || !formData.email) {
            toast.error('Vui lòng điền đầy đủ thông tin mua hàng trước khi thanh toán thẻ.');
            return;
        }

        setIsLoading(true);

        try {
            const token = localStorage.getItem('token');
            const user = JSON.parse(localStorage.getItem('user'));

            const response = await fetch('http://localhost:8000/api/member/payment/stripe/create-intent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
                body: JSON.stringify({
                    amount: totalAmount,
                    user_id: user.id
                }),
            });

            const { clientSecret, error: backendError } = await response.json();

            if (backendError) {
                toast.error(backendError);
                setIsLoading(false);
                return;
            }

            const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
                payment_method: {
                    card: elements.getElement(CardElement),
                    billing_details: {
                        name: formData.name,
                        email: formData.email,
                        phone: formData.phone,
                    },
                },
            });

            if (error) {
                toast.error(error.message);
                setIsLoading(false);
            } else if (paymentIntent.status === 'succeeded') {
                await onPaymentSuccess('Stripe').catch(err => {
                    console.log('Order creation after Stripe success failed (likely validation):', err);
                });
            }
        } catch (err) {
            toast.error('Có lỗi xảy ra trong quá trình thanh toán.');
            console.error(err);
            setIsLoading(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} style={{ padding: '15px', border: '1px solid #ddd', borderRadius: '5px', marginTop: '10px' }}>
            <div style={{ marginBottom: '15px' }}>
                <CardElement options={{
                    style: {
                        base: {
                            fontSize: '16px',
                            color: '#424770',
                            '::placeholder': { color: '#aab7c4' },
                        },
                        invalid: { color: '#9e2146' },
                    },
                }} />
            </div>
            <button
                type="submit"
                disabled={!stripe || isLoading}
                className="order-submit-btn"
                style={{ width: '100%', margin: '0' }}
            >
                {isLoading ? 'Đang xử lý...' : `Thanh toán ${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(totalAmount)}`}
            </button>
        </form>
    );
};

export default StripePaymentForm;
