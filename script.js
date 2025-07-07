function orderProduct(productId) {
    // Show order form modal
    document.getElementById('orderModal').style.display = 'block';
    
    // Set product ID in hidden field
    document.getElementById('product_id').value = productId;
}

function closeModal() {
    document.getElementById('orderModal').style.display = 'none';
}

// Payment method selection
document.querySelectorAll('.payment-method').forEach(method => {
    method.addEventListener('click', function() {
        document.querySelectorAll('.payment-method').forEach(m => {
            m.classList.remove('selected');
        });
        this.classList.add('selected');
        document.getElementById('payment_method').value = this.dataset.method;
    });
});
