<?php
require_once __DIR__ . '/includes/Todo.php';

$todo = new Todo();
$todos = $todo->getAllTodos();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new todo
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        if (!empty($_POST['title'])) {
            $todo->createTodo($_POST['title'], $_POST['description'] ?? '');
        }
        header('Location: /');
        exit;
    }

    // Toggle todo completion
    if (isset($_POST['action']) && $_POST['action'] === 'toggle') {
        $id = $_POST['id'] ?? null;
        $isCompleted = isset($_POST['is_completed']) ? 1 : 0;
        if ($id) {
            $todo->toggleComplete($id, $isCompleted);
        }
        header('Location: /');
        exit;
    }

    // Delete todo
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $todo->deleteTodo($id);
        }
        header('Location: /');
        exit;
    }
}

// Refresh todo list after any action
$todos = $todo->getAllTodos();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --delete-color: #ef233c;
            --completed-color: #4cc9f0;
            --text-color: #212529;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
            --success-color: #38b000;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem;
        }

        h1 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .subtitle {
            color: #6c757d;
            font-size: 1rem;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            font-size: 1.2rem;
            font-weight: 500;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 5px;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
        }

        .btn-primary {
            color: white;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .todo-list {
            margin-top: 1rem;
        }

        .todo-item {
            background-color: white;
            border-radius: 5px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .todo-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .todo-checkbox {
            margin-right: 1rem;
        }

        .custom-checkbox {
            display: inline-block;
            position: relative;
            padding-left: 30px;
            cursor: pointer;
            user-select: none;
        }

        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 22px;
            width: 22px;
            background-color: #eee;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .custom-checkbox:hover input~.checkmark {
            background-color: #ccc;
        }

        .custom-checkbox input:checked~.checkmark {
            background-color: var(--completed-color);
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .custom-checkbox input:checked~.checkmark:after {
            display: block;
        }

        .custom-checkbox .checkmark:after {
            left: 8px;
            top: 4px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .todo-content {
            flex-grow: 1;
            margin-right: 1rem;
        }

        .todo-title {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
            word-break: break-word;
        }

        .todo-description {
            color: #6c757d;
            font-size: 0.9rem;
            word-break: break-word;
        }

        .completed .todo-title {
            text-decoration: line-through;
            color: #6c757d;
        }

        .completed .todo-description {
            text-decoration: line-through;
            color: #adb5bd;
        }

        .todo-actions {
            display: flex;
        }

        .btn-icon {
            background: none;
            border: none;
            color: #6c757d;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-delete {
            color: var(--delete-color);
        }

        .btn-delete:hover {
            color: #d90429;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #adb5bd;
        }

        .empty-message {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .empty-submessage {
            font-size: 0.9rem;
        }

        .created-date {
            font-size: 0.75rem;
            color: #adb5bd;
            margin-top: 0.25rem;
        }

        @media (max-width: 576px) {
            .todo-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .todo-content {
                margin: 0.5rem 0;
                width: 100%;
            }

            .todo-actions {
                width: 100%;
                justify-content: flex-end;
                margin-top: 0.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>Todo List</h1>
            <p class="subtitle">Organize your tasks efficiently</p>
        </header>

        <div class="card">
            <div class="card-header">Add New Task</div>
            <div class="card-body">
                <form method="post" action="/">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <input type="text" name="title" class="form-control" placeholder="What needs to be done?" required>
                    </div>
                    <div class="form-group">
                        <textarea name="description" class="form-control" placeholder="Add details (optional)"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-plus"></i> Add Task
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Your Tasks</div>
            <div class="card-body">
                <div class="todo-list">
                    <?php if (empty($todos)) : ?>
                        <div class="empty-state">
                            <i class="fas fa-clipboard-list"></i>
                            <p class="empty-message">No tasks yet</p>
                            <p class="empty-submessage">Add a new task above to get started</p>
                        </div>
                    <?php else : ?>
                        <?php foreach ($todos as $item) : ?>
                            <div class="todo-item <?= $item['is_completed'] ? 'completed' : '' ?>">
                                <div class="todo-checkbox">
                                    <form method="post" action="/" style="margin: 0;">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <label class="custom-checkbox">
                                            <input type="checkbox" name="is_completed" onChange="this.form.submit()" <?= $item['is_completed'] ? 'checked' : '' ?>>
                                            <span class="checkmark"></span>
                                        </label>
                                    </form>
                                </div>
                                <div class="todo-content">
                                    <div class="todo-title"><?= htmlspecialchars($item['title']) ?></div>
                                    <?php if (!empty($item['description'])) : ?>
                                        <div class="todo-description"><?= htmlspecialchars($item['description']) ?></div>
                                    <?php endif; ?>
                                    <div class="created-date">
                                        Created: <?= date('M j, Y g:i A', strtotime($item['created_at'])) ?>
                                    </div>
                                </div>
                                <div class="todo-actions">
                                    <form method="post" action="/" style="margin: 0;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn-icon btn-delete" title="Delete task">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>