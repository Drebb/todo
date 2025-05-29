-- Create todos table
CREATE TABLE IF NOT EXISTS todos (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert some sample data
INSERT INTO todos (title, description, completed) VALUES
('Learn Docker', 'Set up a Docker environment for the todo app', false),
('Create PHP Backend', 'Build a REST API with PHP for the todo list', false),
('Design Frontend', 'Create a beautiful HTML/CSS/JS frontend', false);

-- Create index for better performance
CREATE INDEX idx_todos_completed ON todos(completed);
CREATE INDEX idx_todos_created_at ON todos(created_at); 