<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .verification-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: successPulse 2s infinite;
        }
        @keyframes successPulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 20px rgba(40, 167, 69, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
        }
        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 123, 255, 0.3);
        }
        .email-icon {
            background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card verification-card">
                    <div class="card-body p-5 text-center">
                        <div class="success-icon">
                            <i class="fas fa-check fa-3x text-white"></i>
                        </div>
                        
                        <h1 class="card-title fw-bold text-success mb-4">Email Verified Successfully!</h1>
                        
                        <div class="email-icon">
                            <i class="fas fa-envelope-check fa-2x"></i>
                        </div>
                        
                        <p class="lead text-muted mb-4">
                            Congratulations! Your email address has been successfully verified.
                        </p>
                        
                        <div class="alert alert-success" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle me-3"></i>
                                <div>
                                    <strong>What's Next?</strong><br>
                                    You can now log in to your account and access all features.
                                </div>
                            </div>
                        </div>
                        
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Your account is now secure and fully activated.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show success animation
        document.addEventListener('DOMContentLoaded', function() {
            // Add confetti effect (simple version)
            setTimeout(function() {
                const colors = ['#28a745', '#20c997', '#007bff', '#ffc107'];
                for (let i = 0; i < 50; i++) {
                    createConfetti(colors[Math.floor(Math.random() * colors.length)]);
                }
            }, 500);
        });

        function createConfetti(color) {
            const confetti = document.createElement('div');
            confetti.style.position = 'fixed';
            confetti.style.left = Math.random() * window.innerWidth + 'px';
            confetti.style.top = '-10px';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.backgroundColor = color;
            confetti.style.pointerEvents = 'none';
            confetti.style.zIndex = '9999';
            confetti.style.borderRadius = '50%';
            
            document.body.appendChild(confetti);
            
            const animation = confetti.animate([
                { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
                { transform: `translateY(${window.innerHeight + 20}px) rotate(720deg)`, opacity: 0 }
            ], {
                duration: 3000,
                easing: 'cubic-bezier(0.5, 0, 0.5, 1)'
            });
            
            animation.onfinish = () => confetti.remove();
        }
        
        // Auto redirect after 15 seconds
        setTimeout(function() {
            window.location.href = "{{ route('login') }}";
        }, 15000);
    </script>
</body>
</html>