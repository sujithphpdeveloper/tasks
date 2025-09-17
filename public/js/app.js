let token = localStorage.getItem('token'); // use this variable for JWT Token for authentication
let activeUser = localStorage.getItem('activeUser');
let activePage = 1;
let currentFilters = {};

const loginSection = document.getElementById('section-login');
const dashboardSection = document.getElementById('section-dashboard');

const loginForm = document.getElementById('login-form');
const loginError = document.getElementById('login-error');
const accountName = document.getElementById('account-name');
const logoutBtn = document.getElementById('logout-btn');

const taskList = document.getElementById('task-list');
const taskPagination = document.getElementById('task-pagination');

const formTag = document.getElementById('form-tag');
const formUser = document.getElementById('form-user');

const filterTag = document.getElementById('filter-tag');
const filterUser = document.getElementById('filter-user');
const filterTitle = document.getElementById('filter-title');
const filterStatus = document.getElementById('filter-status');

const taskModal = new bootstrap.Modal(document.getElementById('task-modal'));
const taskForm = document.getElementById('form-task');
const taskError = document.getElementById('form-error');
const taskModalLabel = document.getElementById('task-modal-label');

// Authentication
const login = async (email, password) => {
    try {
        const response = await axios.post(`${API_BASE_URL}/login`, { email, password });
        token = response.data.access_token;
        localStorage.setItem('token', token);
        setActiveUser();
        loginError.classList.add('d-none');
        showSection(dashboardSection);
        loadData();
    } catch (error) {
        if(error && error.response?.data.message)
            loginError.textContent = error.response.data.message;
        loginError.classList.remove('d-none');
    }
};

// Logout function
const logout = () => {
    token = null;
    activeUser = {};
    localStorage.removeItem('token');
    localStorage.removeItem('activeUser');
    showSection(loginSection);
    loginError.classList.add('d-none');
};

const setActiveUser = async () => {
    if(localStorage.getItem('activeUser') !== undefined) {
        const response = await axios.get(`${API_BASE_URL}/me`, setAuthHeader());
        activeUser = {
            name: response.data.name,
            role: response.data.role
        };
        localStorage.setItem('activeUser', activeUser);
    }
    accountName.innerText = 'Hello ' + activeUser.name ;
    if(activeUser.role !== 'admin') {
        filterUser.classList.add('d-none');
    } else {
        filterUser.classList.remove('d-none');
    }
};
// Common Functions

// Switch the sections based on the Authentication
const showSection = (section) => {
    loginSection.classList.add('d-none');
    dashboardSection.classList.add('d-none');
    section.classList.remove('d-none');
};

const fetchTasks = async () => {
    try {
        setCurrentFilters();
        const params = new URLSearchParams(currentFilters).toString();
        const response = await axios.get(`${API_BASE_URL}/tasks?${params}`, setAuthHeader());
        renderTasks(response.data.data);
        renderPagination(response.data.last_page, response.data.current_page);

    } catch (error) {
        console.error('Failed to fetch tasks:', error);
    }
};

const saveTask = async (task) => {
    try {
        if (task.id) {
            await axios.put(`${API_BASE_URL}/tasks/${task.id}`, task, setAuthHeader());
        } else {
            await axios.post(`${API_BASE_URL}/tasks`, task, setAuthHeader());
        }
        taskError.classList.add('d-none');
        taskModal.hide();
        fetchTasks();
    } catch (error) {
        console.error('Failed to save task:', error);
        if(error && error.response?.data.message)
            taskError.textContent = error.response.data.message;
        taskError.classList.remove('d-none');
    }
};

const deleteTask = async (id) => {
    if (!confirm('Are you sure you want to delete this task?')) return;
    try {
        await axios.delete(`${API_BASE_URL}/tasks/${id}`, setAuthHeader());
        fetchTasks();
    } catch (error) {
        console.error('Failed to delete task:', error);
    }
};

const restoreTask = async (id) => {
    if (!confirm('Are you sure you want to restore this task?')) return;
    try {
        await axios.patch(`${API_BASE_URL}/tasks/${id}/restore`, {}, setAuthHeader());
        fetchTasks();
    } catch (error) {
        console.error('Failed to restore task:', error);
    }
};

const toggleTaskStatus = async (id) => {
    try {
        await axios.patch(`${API_BASE_URL}/tasks/${id}/toggle-status`, {}, setAuthHeader());
        fetchTasks();
    } catch (error) {
        console.error('Failed to toggle status:', error);
    }
};

const fetchTags = async () => {
    try {
        const response = await axios.get(`${API_BASE_URL}/tags`, setAuthHeader());
        populateTagSelects(response.data);
    } catch (error) {
        console.error('Failed to fetch tags:', error);
    }
};

const fetchUsers = async () => {
    try {
        const response = await axios.get(`${API_BASE_URL}/users`, setAuthHeader());
        populateUserSelects(response.data);
    } catch (error) {
        console.error('Failed to fetch users:', error);
    }
};

const renderTasks = (tasks) => {
    taskList.innerHTML = '';
    if (tasks.length === 0) {
        taskList.innerHTML = '<tr><td colspan="6" class="text-center">No tasks found.</td></tr>';
        return;
    }
    tasks.forEach(task => {
        const row = document.createElement('tr');
        const isDeleted = task.deleted_at !== null;
        row.innerHTML += '<td>' + task.id + '</td>';
        row.innerHTML += '<td>' + task.title + '</td>';
        row.innerHTML += '<td>' + task.status + '</td>';
        row.innerHTML += '<td>' + task.priority + '</td>';
        row.innerHTML += '<td>' + (task.user ? task.user.name : 'Not Assigned') + '</td>';
        row.innerHTML += '<td>' + (task.tags.map(tag => '<span class="badge me-1" style="background:' + tag.color + ' ">' + tag.name + '</span>').join('')) + '</td>';

        if(isDeleted) {
            row.innerHTML += '<td>'+ '<button class="btn btn-sm btn-success restore-task" data-id="' + task.id + '">Restore</button>'  +'</td>';
        } else {

            row.innerHTML += '<td><button class="btn btn-sm btn-primary edit-task me-1" data-id="' + task.id + '" data-bs-toggle="modal" data-bs-target="#task-modal">Edit</button>'
                + '<button class="btn btn-sm btn-secondary toggle-status-btn me-1" data-id="' + task.id + '">Update Status</button>'
                + '<button class="btn btn-sm btn-danger delete-task" data-id="'+ task.id +'">Delete</button></td>';
        }
        taskList.appendChild(row);
    });
};

const renderPagination = (last_page, current_page) => {
    if (!last_page || last_page === 1) {
        taskPagination.innerHTML = '';
        return;
    }

    let paginationHtml = '';

    paginationHtml += `<li class="page-item ${current_page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${current_page - 1}">Previous</a>
    </li>`;

    for (let i = 1; i <= last_page; i++) {
        paginationHtml += `<li class="page-item ${i === current_page ? 'active' : ''}">
            <a class="page-link" href="#" data-page="${i}">${i}</a>
        </li>`;
    }

    paginationHtml += `<li class="page-item ${current_page === last_page ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${current_page + 1}">Next</a>
    </li>`;

    taskPagination.innerHTML = paginationHtml;
};

taskPagination.addEventListener('click', (e) => {
    e.preventDefault();
    if (e.target.classList.contains('page-link')) {
        const page = e.target.dataset.page;
        if (page) {
            activePage = page;
            fetchTasks();
        }
    }
});

const setCurrentFilters = () => {

    currentFilters = {};

    currentFilters.page = activePage;

    if(filterTitle.value !== '')
        currentFilters.keyword = filterTitle.value
    if(filterUser.value !== '')
        currentFilters.assigned_to = filterUser.value
    if(filterTag.value !== '')
        currentFilters.tags = [filterTag.value]
    if(filterStatus.value !== '')
        currentFilters.status = filterStatus.value
};

const setAuthHeader = () => {
    return {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    };
};

const loadData = () => {
    setActiveUser();
    fetchTasks();
    fetchTags();
    fetchUsers();
};

// Updating the dropdowns
const populateTagSelects = (tags) => {
    const tagSelects = [filterTag, formTag];
    tagSelects.forEach(select => {
        select.innerHTML = select.id === 'filter-tag' ? '<option value="">All Tags</option>' : '';
        tags.forEach(tag => {
            const option = document.createElement('option');
            option.value = tag.id;
            option.textContent = tag.name;
            select.appendChild(option);
        });
    });
};

const populateUserSelects = (users) => {
    const userSelects = [filterUser, formUser];
    userSelects.forEach(select => {
        select.innerHTML = select.id === 'filter-user' ? '<option value="">All Users</option>' : '<option value="">Select User</option>';
        users.forEach(user => {
            const option = document.createElement('option');
            option.value = user.id;
            option.textContent = user.name;
            select.appendChild(option);
        });
    });
};
//Form actions
document.getElementById('add-task-btn').addEventListener('click', () => {
    taskForm.reset();
    document.getElementById('form-task-id').value = '';
    document.getElementById('form-task-version').value = '1';
    taskModalLabel.textContent = 'Add New Task';
});

taskForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const taskId = document.getElementById('form-task-id').value;
    const taskVersion = document.getElementById('form-task-version').value;
    const taskTitle = document.getElementById('form-title').value;
    const taskDueDate = document.getElementById('form-due-date').value;
    const taskDescription = document.getElementById('form-description').value;
    const taskUser = document.getElementById('form-user').value;
    const taskStatus = document.getElementById('form-status').value;
    const taskPriority = document.getElementById('form-priority').value;
    const taskNote = document.getElementById('form-note').value;
    const taskTags = Array.from(document.getElementById('form-tag').selectedOptions).map(option => option.value);

    const taskData = {
        title: taskTitle,
        description: taskDescription,
        assigned_to: taskUser,
        tags: taskTags,
        due_date: taskDueDate,
        status: taskStatus || undefined,
        priority: taskPriority || undefined,
        version: taskVersion || undefined,
        metadata: {note:taskNote},
        id: taskId || undefined,
    };
    saveTask(taskData);
});

taskList.addEventListener('click', (e) => {
    if (e.target.classList.contains('delete-task')) {
        deleteTask(e.target.dataset.id);
    } else if (e.target.classList.contains('restore-task')) {
        restoreTask(e.target.dataset.id);
    } else if (e.target.classList.contains('toggle-status-btn')) {
        toggleTaskStatus(e.target.dataset.id);
    } else if (e.target.classList.contains('edit-task')) {
        const id = e.target.dataset.id;
        axios.get(`${API_BASE_URL}/tasks/${id}`, setAuthHeader()).then(response => {
            const task = response.data;
            document.getElementById('form-task-id').value = task.id;
            document.getElementById('form-title').value = task.title;
            document.getElementById('form-description').value = task.description;
            document.getElementById('form-user').value = task.assigned_to;
            document.getElementById('form-task-version').value = task.version;
            if(task.due_date !== '') {
                dueDate = task.due_date.split('T');
                document.getElementById('form-due-date').value = dueDate[0];
            }
            document.getElementById('form-status').value = task.status;
            document.getElementById('form-priority').value = task.priority;
            document.getElementById('form-note').value = task.metadata.note;

            Array.from(document.getElementById('form-tag').options).forEach(option => {
                option.selected = task.tags.some(tag => tag.id == option.value);
            });
            taskModalLabel.textContent = 'Edit Task';
        });
    }
});

// Event Listener
loginForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    login(email, password);
});

logoutBtn.addEventListener('click', logout);

// on initial page load will check any token exist then redirect to the dashboard
document.addEventListener('DOMContentLoaded', () => {
    if (token) {
        showSection(dashboardSection);
        loadData();
    } else {
        showSection(loginSection);
    }
});

[filterTitle, filterStatus, filterTag, filterUser].forEach(filter => {
    filter.addEventListener('change', () => {
        activePage = 1;
        fetchTasks();
    });
});


// ---------------over






