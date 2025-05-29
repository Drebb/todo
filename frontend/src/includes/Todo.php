<?php
require_once __DIR__ . '/Database.php';

class Todo
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllTodos()
    {
        $stmt = $this->db->prepare("SELECT * FROM todos ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createTodo($title, $description = '')
    {
        $stmt = $this->db->prepare("INSERT INTO todos (title, description) VALUES (?, ?) RETURNING id");
        $stmt->execute([$title, $description]);
        return $stmt->fetch()['id'];
    }

    public function getTodoById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM todos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateTodo($id, $title, $description, $isCompleted)
    {
        $stmt = $this->db->prepare("UPDATE todos SET title = ?, description = ?, is_completed = ? WHERE id = ?");
        return $stmt->execute([$title, $description, $isCompleted, $id]);
    }

    public function toggleComplete($id, $isCompleted)
    {
        $stmt = $this->db->prepare("UPDATE todos SET is_completed = ? WHERE id = ?");
        return $stmt->execute([$isCompleted, $id]);
    }

    public function deleteTodo($id)
    {
        $stmt = $this->db->prepare("DELETE FROM todos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
