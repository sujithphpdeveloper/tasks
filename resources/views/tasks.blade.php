<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Task Management</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <style>
            .container {
                max-width: 600px;
            }
            .form-label {
                font-weight: bold;
            }
            .error-message {
                color: red;
                font-size: 0.8em;
                margin-top: 5px;
                border: 1px solid red;
                padding: 8px;
                border-radius: 5px;
            }
        </style>
    </head>
    <body class="p-5">
        <div id="section-login" class="container">
            <h2 class="text-center mb-4">Login</h2>
            <form id="login-form">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" required>
                </div>
                <div id="login-error" class="error-message text-center mb-3 d-none">Invalid credentials. Please try again.</div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>

        <div id="section-dashboard" class="d-none">
            <div class="d-flex justify-content-between align-items-center mb-3 text-bg-light px-3 py-3 rounded">
                <h2 class="text-center">Task Management</h2>
                <div class="d-flex align-items-right">
                    <h3 id="account-name" class="me-3">Hello</h3>
                    <button id="logout-btn" class="btn btn-secondary">Logout</button>
                </div>
            </div>

            <div class="text-bg-light px-3 py-3 rounded">
                <div class="mt-3" id="">
                    <div class="tab-pane fade show active" id="tasks" role="tabpanel">
                        <div class="card p-3 mb-3">
                            <h5 class="mb-3">Filters</h5>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" id="filter-title" class="form-control filter-item" placeholder="Search by keyword">
                                </div>
                                <div class="col-md-3">
                                    <select id="filter-tag" class="form-select tag-dropdown filter-item">
                                        <option value="">Filter by Tag</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filter-status" class="form-select filter-item">
                                        <option value="">Filter by Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filter-user" class="form-select filter-item">
                                        <option value="">Filter by User</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#task-modal" id="add-task-btn">Add New Task</button>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Assigned To</th>
                                    <th>Tags</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody id="task-list">
                                </tbody>
                            </table>
                        </div>
                        <nav>
                            <ul class="pagination justify-content-center" id="task-pagination">
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

        </div>

        <div class="modal fade" id="task-modal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="task-modal-label">Add/Edit Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="form-task">
                            <input type="hidden" id="form-task-id">
                            <input type="hidden" id="form-task-version">
                            <div class="row g-3">
                                <div class="mb-2 col-md-6">
                                    <label for="form-title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="form-title" required>
                                </div>
                                <div class="mb-2 col-md-6">
                                    <label for="form-user" class="form-label">User</label>
                                    <select class="form-select" id="form-user">
                                    </select>
                                </div>
                                <div class="mb-2 col-md-6">
                                    <label for="form-status" class="form-label">Status</label>
                                    <select id="form-status" class="form-select">
                                        <option value=""></option>
                                        <option value="pending">Pending</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                                <div class="mb-2 col-md-6">
                                    <label for="form-priority" class="form-label">Priority</label>
                                    <select id="form-priority" class="form-select">
                                        <option value=""></option>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                <div class="mb-2 col-md-6">
                                    <label for="form-due-date" class="form-label">Due Date</label>
                                    <input type="date" class="form-control" id="form-due-date" min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}" >
                                </div>
                                <div class="mb-2 col-md-6">
                                    <label for="form-note" class="form-label">Note</label>
                                    <input type="text" class="form-control" id="form-note" >
                                </div>
                                <div class="mb-2 col-md-6">
                                    <label for="form-description" class="form-label">Description</label>
                                    <textarea class="form-control" id="form-description" rows="3"></textarea>
                                </div>
                                <div class="mb-2 col-md-6">
                                    <label for="form-tag" class="form-label">Tags</label>
                                    <select class="form-select" id="form-tag" multiple>
                                    </select>
                                </div>
                                <div class="mb-2 col-md-12">
                                    <div id="form-error" class="error-message text-center mb-3 d-none"></div>
                                    <button type="submit" class="btn btn-primary w-100">Save Task</button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script>
            const API_BASE_URL = "{{ url('/api/v1') }}";
        </script>
        {{--Not using Laravel Builder for CSS & JS, placing directly on this public folder --}}
        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
