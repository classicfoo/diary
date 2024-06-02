document.addEventListener('DOMContentLoaded', function() {
    // Select all entry elements
    var entries = document.querySelectorAll('.entry');
  
    // Attach click event listeners to each entry
    entries.forEach(function(entry) {
      entry.addEventListener('click', function() {
        // Retrieve and store only the entry ID
        var entryId = this.getAttribute('data-entry-id');
        localStorage.setItem('selectedEntryId', entryId);
        // Redirect to the entry-edit page
        window.location.href = 'entry-edit.html';
      });
    });
  
    // Add event listener to the "Add Entry" button
    document.getElementById('add-entry').addEventListener('click', function() {
      // Clear the selected entry ID when adding a new entry
      localStorage.removeItem('selectedEntryId');
      // Redirect to the entry-edit page
      window.location.href = 'entry-edit.html';
    });
  
    document.getElementById('entry-filter').addEventListener('input', filterEntries);
  
    updateEntryListDisplay();
  });
  
  function filterEntries() {
    const query = document.getElementById('entry-filter').value.toLowerCase();
    const keywords = query.split(' '); // Split the input into multiple keywords
    const entries = JSON.parse(localStorage.getItem('entries')) || [];
  
    const filteredEntries = entries.filter(entry => {
      // Check if all keywords are present in the entry content
      return keywords.every(keyword => entry.content.toLowerCase().includes(keyword));
    });
  
    updateEntryListDisplay(filteredEntries);
  }
  
  function updateEntryListDisplay(entriesToShow) {
    const entryListContainer = document.getElementById('entry-list');
    if (entryListContainer) {
      entryListContainer.innerHTML = '';
  
      let entries = entriesToShow || JSON.parse(localStorage.getItem('entries')) || [];
      entries.sort((a, b) => new Date(b.date) - new Date(a.date)); // Sort by date in descending order
  
      entries.forEach(entry => {
        const entryElement = document.createElement('div');
        entryElement.classList.add('entry');
        entryElement.setAttribute('data-entry-id', entry.id);
  
        const entryDate = new Date(entry.date);
        const dateText = entryDate.toLocaleDateString(); // Format the date
  
        entryElement.innerHTML = `
          <div class="entry-info">
            <span class="entry-date">${dateText}</span>
            <span class="entry-content">${entry.content}</span>
          </div>
        `;
  
        entryElement.addEventListener('click', function() {
          localStorage.setItem('selectedEntryId', entry.id);
          window.location.href = 'entry-edit.html';
        });
  
        entryListContainer.appendChild(entryElement);
      });
    } else {
      console.error("Entry list container not found");
    }
  }