{{-- resources/views/root-manager/secret/secret_page.blade.php --}}

@extends('components.admin.content-layout')

@section('card-content')

<!-- Login Modal -->
<div class="modal fade" id="codeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-header border-0 pt-4 px-4">
                <div class="text-center w-100">
                    <div class="mb-2">
                        <i class="fas fa-key" style="font-size: 48px; color: #3b82f6;"></i>
                    </div>
                    <h5 class="modal-title fw-bold text-primary">🔐 Enter Secret Code</h5>
                    <p class="text-muted small mb-0">Enter your secret code to access the dashboard</p>
                </div>
            </div>

            <div class="modal-body px-4 pb-4">
                <form id="loginForm">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Secret Code / Password</label>
                        <input type="password" 
                               class="form-control form-control-lg rounded-3" 
                               name="code_value" 
                               id="codeValueInput"
                               placeholder="Enter your secret code"
                               autocomplete="off"
                               autofocus>
                        <div class="text-danger mt-2 small" id="codeError"></div>
                        <div class="form-text mt-1">
                            <small>Contact administrator if you don't have a code.</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <button type="button" class="btn btn-outline-secondary px-4 py-2" id="resetBtn">Reset</button>
                        <button type="submit" class="btn btn-primary px-5 py-2" id="submitBtn">
                            <span id="btnText">Access Dashboard</span>
                            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
        <div class="toast-header">
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            Message here
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    .modal-dialog {
        max-width: 450px;
    }

    .modal-content {
        border-radius: 20px !important;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }

    .form-control {
        transition: all 0.2s ease;
        border: 2px solid #e2e8f0;
        font-size: 1rem;
        padding: 12px 15px;
    }

    .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }

    .form-control.is-invalid {
        border-color: #dc3545;
        background-image: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-primary:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(37,99,235,0.3);
    }
    
    .btn-primary:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    .toast {
        min-width: 300px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
</style>
@endsection

@section('js')
<script>
document.addEventListener("DOMContentLoaded", function () {
    
    const modalElement = document.getElementById('codeModal');
    const form = document.getElementById('loginForm');
    const codeValueInput = document.getElementById('codeValueInput');
    const errorDiv = document.getElementById('codeError');
    const resetBtn = document.getElementById('resetBtn');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    let modalInstance = null;

    // Show modal
    modalInstance = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: false
    });
    modalInstance.show();

    // Clear error on input
    codeValueInput.addEventListener('input', function() {
        errorDiv.innerHTML = '';
        codeValueInput.classList.remove('is-invalid');
    });

    // Reset button
    resetBtn.addEventListener('click', function() {
        codeValueInput.value = '';
        errorDiv.innerHTML = '';
        codeValueInput.classList.remove('is-invalid');
        codeValueInput.focus();
    });

    // Function to show toast messages
    function showToast(message, type = 'success') {
        const toastElement = document.getElementById('liveToast');
        const toastTitle = document.getElementById('toastTitle');
        const toastMessage = document.getElementById('toastMessage');
        
        if (type === 'success') {
            toastTitle.textContent = '✅ Success!';
            toastTitle.style.color = '#28a745';
            toastMessage.textContent = message;
        } else {
            toastTitle.textContent = '❌ Error!';
            toastTitle.style.color = '#dc3545';
            toastMessage.textContent = message;
        }
        
        const toast = new bootstrap.Toast(toastElement, {
            animation: true,
            autohide: true,
            delay: 3000
        });
        toast.show();
    }

    // Form submission
    form.addEventListener("submit", async function(e) {
        e.preventDefault();
        
        const codeValue = codeValueInput.value.trim();
        
        // Clear previous errors
        errorDiv.innerHTML = '';
        codeValueInput.classList.remove('is-invalid');
        
        // Validation
        if (codeValue === "") {
            errorDiv.innerHTML = "❌ Please enter secret code";
            codeValueInput.classList.add('is-invalid');
            codeValueInput.focus();
            return;
        }
        
        if (codeValue.length < 3) {
            errorDiv.innerHTML = "❌ Secret code must be at least 3 characters";
            codeValueInput.classList.add('is-invalid');
            codeValueInput.focus();
            return;
        }

        // Show loading
        submitBtn.disabled = true;
        btnText.classList.add('d-none');
        btnSpinner.classList.remove('d-none');

        try {
            // Send AJAX request
            const response = await fetch('{{ route("secret.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    code_value: codeValue
                })
            });

            const result = await response.json();

            if (result.success) {
                showToast(result.message, 'success');
                        // Redirect to dashboard after 1 second
                        setTimeout(() => {
                window.open(result.data.redirect_url, '_blank');
                
                // Optional: Close current modal or redirect current tab to somewhere else
                // window.location.href = '/'; // redirect current tab to home
            }, 1000);
            } else {
                showToast(result.message, 'error');
                // Reset button state
                submitBtn.disabled = false;
                btnText.classList.remove('d-none');
                btnSpinner.classList.add('d-none');
                codeValueInput.value = '';
                codeValueInput.focus();
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Network error. Please try again.', 'error');
            // Reset button state
            submitBtn.disabled = false;
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
        }
    });
});
</script>
@endsection