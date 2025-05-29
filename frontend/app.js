class TodoApp {
    constructor() {
        this.apiUrl = '/api/todos';
        this.todos = [];
        this.currentFilter = 'all';
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadTodos();
        this.checkServiceStatus();
        
        // Check backend status every 10 seconds
        setInterval(() => this.checkServiceStatus(), 10000);
    }

    bindEvents() {
        const addBtn = document.getElementById('addTodo');
        const titleInput = document.getElementById('todoTitle');
        const clearBtn = document.getElementById('clearCompleted');
        const filterBtns = document.querySelectorAll('.filter-btn');

        addBtn.addEventListener('click', () => this.addTodo());
        titleInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.addTodo();
        });
        clearBtn.addEventListener('click', () => this.clearCompleted());

        filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.setFilter(e.target.dataset.filter);
            });
        });
    }

    async checkServiceStatus() {
        try {
            const response = await fetch('/api/health');
            if (response.ok) {
                this.updateServiceStatus('backend', true);
                this.updateServiceStatus('db', true);
            } else {
                throw new Error('Backend not responding');
            }
        } catch (error) {
            this.updateServiceStatus('backend', false);
            this.updateServiceStatus('db', false);
        }
    }

    updateServiceStatus(service, isOnline) {
        const dot = document.getElementById(`${service}-dot`);
        if (dot) {
            dot.className = `status-dot ${isOnline ? 'online' : 'offline'}`;
        }
    }

    showLoading(show = true) {
        const loading = document.getElementById('loading');
        loading.style.display = show ? 'block' : 'none';
    }

    showError(message) {
        const errorEl = document.getElementById('errorMessage');
        const span = errorEl.querySelector('span');
        span.textContent = message;
        errorEl.style.display = 'flex';
        
        setTimeout(() => {
            errorEl.style.display = 'none';
        }, 5000);
    }

    async apiCall(endpoint, options = {}) {
        try {
            const response = await fetch(`${this.apiUrl}${endpoint}`, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('API call failed:', error);
            if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
                this.showError('Unable to connect to backend. Please check if the backend service is running.');
            } else {
                this.showError(`API Error: ${error.message}`);
            }
            throw error;
        }
    }

    async loadTodos() {
        try {
            this.showLoading(true);
            const todos = await this.apiCall('');
            this.todos = todos || [];
            this.renderTodos();
            this.updateStats();
        } catch (error) {
            console.error('Failed to load todos:', error);
        } finally {
            this.showLoading(false);
        }
    }

    async addTodo() {
        const titleInput = document.getElementById('todoTitle');
        const descriptionInput = document.getElementById('todoDescription');
        
        const title = titleInput.value.trim();
        const description = descriptionInput.value.trim();

        if (!title) {
            this.showError('Please enter a todo title');
            return;
        }

        try {
            await this.apiCall('', {
                method: 'POST',
                body: JSON.stringify({
                    title,
                    description,
                    completed: false
                })
            });

            titleInput.value = '';
            descriptionInput.value = '';
            await this.loadTodos();
        } catch (error) {
            console.error('Failed to add todo:', error);
        }
    }

    async toggleTodo(id) {
        const todo = this.todos.find(t => t.id == id);
        if (!todo) return;

        try {
            await this.apiCall('', {
                method: 'PUT',
                body: JSON.stringify({
                    id: todo.id,
                    title: todo.title,
                    description: todo.description,
                    completed: !todo.completed
                })
            });

            await this.loadTodos();
        } catch (error) {
            console.error('Failed to toggle todo:', error);
        }
    }

    async deleteTodo(id) {
        if (!confirm('Are you sure you want to delete this todo?')) {
            return;
        }

        try {
            await this.apiCall(`?id=${id}`, {
                method: 'DELETE'
            });

            await this.loadTodos();
        } catch (error) {
            console.error('Failed to delete todo:', error);
        }
    }

    async clearCompleted() {
        const completedTodos = this.todos.filter(todo => todo.completed);
        
        if (completedTodos.length === 0) {
            this.showError('No completed todos to clear');
            return;
        }

        if (!confirm(`Delete ${completedTodos.length} completed todo(s)?`)) {
            return;
        }

        try {
            for (const todo of completedTodos) {
                await this.apiCall(`?id=${todo.id}`, {
                    method: 'DELETE'
                });
            }
            await this.loadTodos();
        } catch (error) {
            console.error('Failed to clear completed todos:', error);
        }
    }

    setFilter(filter) {
        this.currentFilter = filter;
        
        // Update active filter button
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-filter="${filter}"]`).classList.add('active');
        
        this.renderTodos();
    }

    getFilteredTodos() {
        switch (this.currentFilter) {
            case 'active':
                return this.todos.filter(todo => !todo.completed);
            case 'completed':
                return this.todos.filter(todo => todo.completed);
            default:
                return this.todos;
        }
    }

    renderTodos() {
        const todoList = document.getElementById('todoList');
        const filteredTodos = this.getFilteredTodos();

        if (filteredTodos.length === 0) {
            todoList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No todos found</h3>
                    <p>${this.currentFilter === 'all' ? 'Add your first todo above!' : `No ${this.currentFilter} todos.`}</p>
                </div>
            `;
            return;
        }

        todoList.innerHTML = filteredTodos.map(todo => `
            <div class="todo-item ${todo.completed ? 'completed' : ''}">
                <input 
                    type="checkbox" 
                    class="todo-checkbox" 
                    ${todo.completed ? 'checked' : ''}
                    onchange="app.toggleTodo(${todo.id})"
                >
                <div class="todo-content">
                    <div class="todo-title">${this.escapeHtml(todo.title)}</div>
                    ${todo.description ? `<div class="todo-description">${this.escapeHtml(todo.description)}</div>` : ''}
                    <div class="todo-meta">
                        Created: ${new Date(todo.created_at).toLocaleDateString()}
                        ${todo.updated_at !== todo.created_at ? `• Updated: ${new Date(todo.updated_at).toLocaleDateString()}` : ''}
                    </div>
                </div>
                <div class="todo-actions">
                    <button class="action-btn delete" onclick="app.deleteTodo(${todo.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }

    updateStats() {
        const total = this.todos.length;
        const completed = this.todos.filter(todo => todo.completed).length;
        const active = total - completed;

        const countEl = document.getElementById('todoCount');
        countEl.textContent = `${total} total, ${active} active, ${completed} completed`;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.app = new TodoApp();
}); 