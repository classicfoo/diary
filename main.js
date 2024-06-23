
document.addEventListener('DOMContentLoaded', function() {
    
    // Select all task elements
    var tasks = document.querySelectorAll('.task');

    // Attach click event listeners to each task
    tasks.forEach(function(task) {
        task.addEventListener('click', function() {
            // Retrieve and store only the task ID
            var taskId = this.getAttribute('data-task-id');
            localStorage.setItem('selectedTaskId', taskId);
            // Redirect to the task-edit page
            window.location.href = 'task-edit.html';
        });
    });

    // Add event listener to the "Add Task" button
    document.getElementById('add-task').addEventListener('click', function() {
        // Clear the selected task ID when adding a new task
        localStorage.removeItem('selectedTaskId');
        // Redirect to the task-edit page
        window.location.href = 'task-edit.html';
    });

    document.getElementById('task-filter').addEventListener('input', filterTasks);

    updateTaskListDisplay();

});



function filterTasks() {
    const query = document.getElementById('task-filter').value.toLowerCase();
    const keywords = query.split(' '); // Split the input into multiple keywords
    const tasks = JSON.parse(localStorage.getItem('tasks')) || [];

    const filteredTasks = tasks.filter(task => {
        // Check if all keywords are present in the task title
        return keywords.every(keyword => task.title.toLowerCase().includes(keyword));
    });

    updateTaskListDisplay(filteredTasks); // Update this to accept a tasks parameter
}


function updateTaskListDisplay(tasksToShow) {
    const taskListContainer = document.getElementById('task-list');
    if (taskListContainer) {
        taskListContainer.innerHTML = '';

        /*
        //sort by date then by title
        let tasks = tasksToShow || JSON.parse(localStorage.getItem('tasks')) || [];
        tasks.sort((a, b) => {
            const dateComparison = new Date(a.date) - new Date(b.date);
            if (dateComparison === 0) { // If dates are the same, sort by title
                return a.title.localeCompare(b.title);
            }
            return dateComparison;
        });*/

        
        //sort by date then by id
        let tasks = tasksToShow || JSON.parse(localStorage.getItem('tasks')) || [];
        tasks.sort((a, b) => {
            const dateComparison = new Date(a.date) - new Date(b.date);
            if (dateComparison === 0) { // If dates are the same, sort by title
                return b.id.localeCompare(a.id);
            }
            return dateComparison;
        });
        
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);

        tasks.forEach(task => {
            const taskElement = document.createElement('div');
            taskElement.classList.add('task');
            taskElement.setAttribute('data-task-id', task.id);

            const taskDate = new Date(task.date);
            let dateText;

            if(taskDate.toDateString() === today.toDateString()) {
                dateText = 'Today';
                taskElement.classList.add('bg-green');
            } else if(taskDate.toDateString() === tomorrow.toDateString()) {
                dateText = 'Tomorrow';
                taskElement.classList.add('bg-blue');
            } else if(taskDate > tomorrow) {
                dateText = 'Later';
                taskElement.classList.add('bg-light-blue');
            } else {
                dateText = 'Overdue';
                taskElement.classList.add('bg-red');
            }

            // Create a container for the date and title with flexbox layout
            const taskInfo = document.createElement('div');
            taskInfo.classList.add('task-info');

            taskInfo.innerHTML = `
                <span class="task-date">${dateText}</span>
                <span class="task-title">${task.title}</span>
            `;
            
            taskElement.addEventListener('click', function() {
                //clear the filter before user clicks on an task, so when user goes back filter value is cleared
                document.getElementById('task-filter').value = "";

                localStorage.setItem('selectedTaskId', task.id);
                window.location.href = 'task-edit.html';
            });
            taskElement.appendChild(taskInfo);
            taskListContainer.appendChild(taskElement);
        });
    } else {
        console.error("Task list container not found");
    }

    //handle task completed
    if (localStorage.getItem('taskCompleted') === 'true') {
        triggerConfetti();
        localStorage.removeItem('taskCompleted');
    }

    function triggerConfetti() {
        confetti({
            angle: 60,
            spread: 55,
            particleCount: 100,
            origin: { y: 0.6 }
        });
        confetti({
            angle: 120,
            spread: 55,
            particleCount: 100,
            origin: { y: 0.6 }
        });
    }
    
    /* This animation is cool but distracting
    // Assuming you have a way to select all the tasks, like a class name
    const tasks = document.querySelectorAll('.task');

    tasks.forEach((task, index) => {
        const delay = index * 0.15; // 0.5 seconds delay per task
        task.style.animation = `slideInFromLeft 0.25s ease-out ${delay}s forwards`;
    });
    */
}





