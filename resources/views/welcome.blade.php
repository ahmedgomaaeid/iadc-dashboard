@extends('layouts.landing')

@section('header')
    @include('landing.header')
@endsection

@section('content')
    <div class="">

        @include('landing.statstics')

        @include('landing.about')

        @include('landing.events')

        @include('landing.blog')

        
        @include('landing.magazin')
       
        @include('landing.team')

        @include('landing.contact')
    </div>
@endsection

@section('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const submitBtn = document.getElementById('contact-submit');
        const submitText = document.getElementById('submit-text');
        const submitLoading = document.getElementById('submit-loading');
        
        // Validate fields
        const name = document.getElementById('contact-name').value.trim();
        const email = document.getElementById('contact-email').value.trim();
        const message = document.getElementById('contact-message').value.trim();
        
        if (!name || !email || !message) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please fill in all required fields.',
                confirmButtonColor: '#ab1f2e'
            });
            return;
        }
        
        // Show loading
        submitBtn.disabled = true;
        submitText.classList.add('d-none');
        submitLoading.classList.remove('d-none');
        
        fetch('{{ route("contact.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({
                name: name,
                email: email,
                message: message
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Message Sent!',
                    text: data.message,
                    confirmButtonColor: '#ab1f2e'
                });
                form.reset();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: data.message || 'Something went wrong. Please try again.',
                    confirmButtonColor: '#ab1f2e'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Something went wrong. Please try again.',
                confirmButtonColor: '#ab1f2e'
            });
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitText.classList.remove('d-none');
            submitLoading.classList.add('d-none');
        });
    });
</script>
@endsection