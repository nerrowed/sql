/**
 * INSIDER Training Platform - JavaScript
 * For educational purposes only
 */

// Confirmation for destructive actions
function confirmAction(message) {
    return confirm(message);
}

// AJAX function to get hints
function getHint(challengeId, hintLevel) {
    const xhr = new XMLHttpRequest();
    const baseUrl = '/sql';
    xhr.open('POST', baseUrl + '/hints/get_hint.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    displayHint(response.hint, hintLevel);
                } else {
                    alert('Error: ' + response.message);
                }
            } catch (e) {
                alert('Error parsing response');
            }
        }
    };
    
    xhr.send('challenge_id=' + challengeId + '&hint_level=' + hintLevel);
}

// Display hint in the UI
function displayHint(hintText, level) {
    const hintContainer = document.getElementById('hint-container');
    if (hintContainer) {
        const hintDiv = document.createElement('div');
        hintDiv.className = 'success-box';
        hintDiv.style.marginTop = '15px';
        hintDiv.innerHTML = '<strong>💡 Hint ' + level + ':</strong> ' + hintText;
        hintContainer.appendChild(hintDiv);
    }
}

// Toggle hint visibility
function toggleHints() {
    const hintSection = document.getElementById('hint-section');
    if (hintSection) {
        hintSection.style.display = hintSection.style.display === 'none' ? 'block' : 'none';
    }
}

// Syntax highlighting for SQL queries (basic)
document.addEventListener('DOMContentLoaded', function() {
    const queryDisplays = document.querySelectorAll('.query-display pre');
    queryDisplays.forEach(function(pre) {
        let text = pre.textContent;
        
        // Highlight SQL keywords
        const keywords = ['SELECT', 'FROM', 'WHERE', 'AND', 'OR', 'INSERT', 'UPDATE', 'DELETE', 
                         'UNION', 'ORDER BY', 'LIKE', 'JOIN', 'LIMIT', 'GROUP BY'];
        
        keywords.forEach(function(keyword) {
            const regex = new RegExp('\\b' + keyword + '\\b', 'gi');
            text = text.replace(regex, '<span style="color: #3498db; font-weight: bold;">' + keyword + '</span>');
        });
        
        pre.innerHTML = text;
    });
});

// Form validation (minimal, for UX)
function validateLoginForm() {
    const username = document.getElementById('username');
    const password = document.getElementById('password');
    
    if (!username || !password) return true;
    
    if (username.value.trim() === '') {
        alert('Please enter a username');
        return false;
    }
    
    if (password.value.trim() === '') {
        alert('Please enter a password');
        return false;
    }
    
    return true;
}

// Database reset confirmation
function confirmDatabaseReset() {
    return confirm('Are you sure you want to reset the database? This will restore all tables to their initial state.');
}

// Progress reset confirmation
function confirmProgressReset(username) {
    return confirm('Are you sure you want to reset progress for user: ' + username + '?');
}
