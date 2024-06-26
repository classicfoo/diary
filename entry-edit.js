let selectionStart = 0;
let selectionEnd = 0;

function saveSelection() {
  const textarea = document.getElementById("entry-content");
  selectionStart = textarea.selectionStart;
  selectionEnd = textarea.selectionEnd;

  if (selectionStart === selectionEnd) {
    alert("Please select some text before indenting.");
  }
}

function restoreSelection() {
  const textarea = document.getElementById("entry-content");
  textarea.setSelectionRange(selectionStart, selectionEnd);
  textarea.focus();
}

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

  document.getElementById("format-text").addEventListener("click", function () {
    formatTextHandler();
  });

  function formatTextHandler() {
    sentenceCaseWithBullets();
    saveEntry();
    // You can add more formatting functions here in the future
  }

  function sentenceCaseWithBullets() {
    let allText = document.getElementById('entry-content').value;
    
    if (allText) {
      let lines = allText.split('\n');
      let capitalizedLines = [];
  
      // Loop through each line in the input text
      for (let line of lines) {
        let trimmedLine = line.trim(); // Trim leading and trailing spaces

        // Line starts with a letter followed by a space, apply sentence case with bullets
        if (/^[a-zA-Z]\s/.test(trimmedLine)) {
          let preservedSpaces = line.match(/^\s*/)[0]; // Preserve leading spaces

          // Capitalize the third character of the trimmed line
          trimmedLine = preservedSpaces + trimmedLine.slice(0, 2) + trimmedLine[2].toUpperCase() + trimmedLine.slice(3);
        }
        else {
          // Line does not match the specified format, capitalize the first word
          trimmedLine = trimmedLine.charAt(0).toUpperCase() + trimmedLine.slice(1);
        }

      // Add the processed line to the list of capitalized lines
      capitalizedLines.push(trimmedLine);
  
      let capitalizedText = capitalizedLines.join('\n');
  
      let sentenceCasedText = [];
      for (let line of capitalizedLines) {
        let sentences = line.split(/(?<=[.!?])\s+/);
        let sentenceCasedSentences = [];
  
        for (let sentence of sentences) {
          if (sentence) {
            sentence = sentence.charAt(0).toUpperCase() + sentence.slice(1);
            sentenceCasedSentences.push(sentence);
          }
        }
  
        let sentenceCasedLine = sentenceCasedSentences.join(' ');
        sentenceCasedText.push(sentenceCasedLine);
      }
  
      sentenceCasedText = sentenceCasedText.join('\n');
  
      document.getElementById('entry-content').value = sentenceCasedText;
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
      saveEntry();
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

  // Add an event listener for the indent-text button
  document.getElementById("indent-text").addEventListener("click", function () {
    saveSelection();
    indentTextHandler();

    const textarea = document.getElementById("entry-content");
    selectionEnd = textarea.selectionEnd;
    restoreSelection();
  });

  // Add an event listener for the unindent-text button
document.getElementById("unindent-text").addEventListener("click", function () {
  saveSelection();
  unindentTextHandler();
  
  const textarea = document.getElementById("entry-content");
  selectionEnd = textarea.selectionEnd +2;
  restoreSelection();
});

  function indentTextHandler() {
    const textarea = document.getElementById("entry-content");
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
  
    // Get the selected text
    const selectedText = text.substring(start, end);
  
    // Split the selected text into lines
    const lines = selectedText.split('\n');
  
    // Prepend two spaces to each line
    const indentedLines = lines.map(line => '  ' + line);
  
    // Join the lines back into a single string
    const indentedText = indentedLines.join('\n');
  
    // Replace the selected text with the indented text
    textarea.setRangeText(indentedText, start, end, "end");
  
    // Trigger the input event to save the task and update UI
    saveEntry();
    textarea.dispatchEvent(new Event("input"));
  }

  function unindentTextHandler() {
    const textarea = document.getElementById("entry-content");
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
  
    // Split the text into lines
    const lines = text.substring(start, end).split('\n');
  
    // Unindent each line
    const unindentedLines = lines.map(line => {
      if (line.startsWith('  ')) {
        return line.substring(2);
      }
      return line;
    });
  
    // Join the lines back together
    const newText = unindentedLines.join('\n');
  
    // Replace the selected text with the unindented text
    textarea.value = text.substring(0, start) + newText + text.substring(end);

    // Trigger the input event to save the task and update UI
    saveEntry();

    // Adjust the selection range
    textarea.setSelectionRange(start, start + newText.length);
  }

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
