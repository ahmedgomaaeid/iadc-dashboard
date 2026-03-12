<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $dynamicForm->title }} - Submissions</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/IADC Icon.png') }}">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
        }
        .header {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 15px 0;
            margin-bottom: 30px;
        }
        .logo {
            height: 50px;
        }
        .table-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header-custom h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .card-body-custom {
            padding: 25px;
        }
        .table th {
            background-color: #f9fafb;
            color: #4b5563;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.05em;
            padding: 15px;
        }
        .table td {
            vertical-align: middle;
            padding: 15px;
            color: #374151;
        }
        .btn-export {
            background-color: #10b981;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-export:hover {
            background-color: #059669;
            color: white;
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="container d-flex justify-content-center">
            <img src="{{ asset('images/logo.png') }}" alt="IADC Logo" class="logo">
        </div>
    </header>

    <div class="container-fluid px-4 pb-5">
        <div class="table-card">
            <div class="card-header-custom">
                <div>
                    <h2>{{ $dynamicForm->title }}</h2>
                    <p class="mb-0 text-white-50">{{ $dynamicForm->subtitle }} - Submissions</p>
                </div>
                <a href="{{ route('shared-forms.submissions.export', $encryptedId) }}" class="btn btn-export">
                    <i class="fas fa-file-excel me-2"></i>Export to Excel
                </a>
            </div>
            
            <div class="card-body-custom">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Submitted At</th>
                                @foreach($dynamicForm->getOrderedFields() as $fieldName => $field)
                                    <th>{{ $field['label'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $index => $submission)
                                <tr>
                                    <td>{{ $submissions->firstItem() + $index }}</td>
                                    <td>{{ $submission->created_at->format('M d, Y h:i A') }}</td>
                                    @foreach($dynamicForm->getOrderedFields() as $fieldName => $field)
                                        <td>
                                            @php
                                                $value = $submission->data[$fieldName] ?? '-';
                                            @endphp
                                            {{ is_array($value) ? implode(', ', $value) : $value }}
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($dynamicForm->getOrderedFields()) + 2 }}" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 text-light"></i>
                                        <p class="mb-0 fs-5">No submissions found yet.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    {{ $submissions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
