document.addEventListener('DOMContentLoaded', function() {
  var entryId = localStorage.getItem('selectedEntryId');

  // Set the entry date to today's date by default
  const dateInput = document.getElementById('entry-date');
  const today = new Date();
  const formattedDate = today.toISOString().substr(0, 10); // Formats the date to YYYY-MM-DD
  dateInput.value = formattedDate;

  if (entryId) {
    // Load entry details from local storage or your data source
    const entries = JSON.parse(localStorage.getItem('entries')) || [];
    const selectedEntry = entries.find(entry => entry.id === entryId);

    if (selectedEntry) {
      // Set the entry details in the form
      document.getElementById('entry-content').value = selectedEntry.content;

      // Set the entry date if it exists
      if (selectedEntry.date) {
        dateInput.value = selectedEntry.date;
      }
    }
  }

  
  function handleKeyDown(event) {
    // Check if the Enter key was pressed
    if (event.key === "Enter" || event.keyCode === 13) {
      const textarea = document.getElementById("entry-content");
      const cursorPosition = textarea.selectionStart;
      const text = textarea.value;

      // Find the start and end indices of the current line
      let lineStart = text.lastIndexOf("\n", cursorPosition - 1) + 1;
      let lineEnd = text.indexOf("\n", cursorPosition);
      lineEnd = lineEnd === -1 ? text.length : lineEnd;
      const currentLine = text.substring(lineStart, lineEnd);

      // Check if the current line contains only spaces
      if (/^\s*$/.test(currentLine)) {
        // If the line contains only spaces, insert a new line without indentation
        const nextLine = "\n";
        textarea.setRangeText(nextLine, cursorPosition, cursorPosition, "end");
      } else {
        // Count the number of leading spaces in the current line
        const leadingSpaces = currentLine.match(/^\s*/)[0];

        // Insert the same number of spaces at the beginning of the next line
        const nextLine = "\n" + leadingSpaces;

        // Insert the spaces at the cursor position
        textarea.setRangeText(nextLine, cursorPosition, cursorPosition, "end");
      }

      // Prevent the default behavior of the Enter key
      event.preventDefault();

      // Trigger the input event to save the task and update UI
      saveTask();
      textarea.dispatchEvent(new Event("input"));
    }
  }

  // Function to handle input events
  function handleInputChange() {
    saveEntry();
  }

  // Add input event listeners to the entry content and date fields
  document.getElementById('entry-content').addEventListener('input', handleInputChange);
  document.getElementById('entry-date').addEventListener('input', handleInputChange);
  document.getElementById("entry-content").addEventListener("keydown", handleKeyDown);

  // Event listener for the delete button
  document.getElementById('delete-entry').addEventListener('click', function() {
    const confirmDelete = confirm('Are you sure you want to delete this entry?');
    if (confirmDelete) {
      const entries = JSON.parse(localStorage.getItem('entries')) || [];
      const entryIndex = entries.findIndex(entry => entry.id === entryId);

      if (entryIndex !== -1) {
        entries.splice(entryIndex, 1);
        localStorage.setItem('entries', JSON.stringify(entries));
        window.location.href = 'index.html';
      } else {
        console.error('Entry not found in the array.');
      }

      localStorage.removeItem('selectedEntryId');
    }
  });

  // Function to save entry
  function saveEntry() {
    const entryContent = document.getElementById('entry-content').value;
    const entryDate = document.getElementById('entry-date').value;
    const entries = JSON.parse(localStorage.getItem('entries')) || [];

    if (entryId) {
      // Update existing entry
      const entryIndex = entries.findIndex(entry => entry.id === entryId);
      if (entryIndex !== -1) {
        entries[entryIndex].content = entryContent;
        entries[entryIndex].date = entryDate;
      } else {
        console.error('Entry not found.');
        return;
      }
    } else {
      // Create new entry
      const newEntryId = new Date().getTime().toString(); // Example ID generation
      const newEntry = {
        id: newEntryId,
        content: entryContent,
        date: entryDate,
      };
      entries.push(newEntry);

      // Update the selectedEntryId in localStorage
      localStorage.setItem('selectedEntryId', newEntryId);
      entryId = newEntryId; // Update entryId to prevent creating new entries on every input
    }

    // Save back to localStorage
    localStorage.setItem('entries', JSON.stringify(entries));
  }
});
