<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quiz->name }} - Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/IADC Icon.png') }}">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f3f4f6; }
        .registration-container { max-width: 600px; margin: 100px auto; padding: 20px; }
        .registration-card { background: #fff; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%); color: white; padding: 30px; text-align: center; }
        .card-header h2 { font-weight: 600; margin-bottom: 5px; }
        .card-body { padding: 30px; }
        .btn-register { background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%); color: #fff; border-radius: 10px; padding: 12px; font-weight: 600; font-size: 1.1rem; border: none; width: 100%; transition: transform 0.2s; }
        .btn-register:hover { transform: translateY(-2px); color: #fff; }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-card">
            <div class="card-header">
                <h2><i class="fas fa-play-circle me-2"></i>Live Interactive Quiz</h2>
                <p class="mb-0">{{ $quiz->name }}</p>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
                @endif
                <form action="{{ route('interactive_quiz.join', $quiz) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="form-label fw-bold">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control form-control-lg" placeholder="John Doe" required>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control form-control-lg" placeholder="john@example.com" required>
                    </div>
                    <button type="submit" class="btn btn-register shadow"><i class="fas fa-sign-in-alt me-2"></i>Join Quiz</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
