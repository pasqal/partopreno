/**
 * Script JavaScript pour le système d'inscription
 */

// ============================================
// Fonctions utilitaires
// ============================================

/**
 * Afficher un message de notification
 * @param {string} message - Message à afficher
 * @param {string} type - Type de message (success, error, info, warning)
 */
function showNotification(message, type = 'info') {
    // Créer l'élément de notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Ajouter des styles dynamiquement
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 4px;
        color: white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 1rem;
        animation: slideIn 0.3s ease-out;
        background-color: ${getNotificationColor(type)};
    `;
    
    // Style pour le bouton de fermeture
    const closeBtn = notification.querySelector('button');
    closeBtn.style.cssText = `
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    `;
    
    // Ajouter l'animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    
    // Ajouter la notification au DOM
    document.body.appendChild(notification);
    
    // Supprimer après 5 secondes
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out forwards';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

/**
 * Obtenir la couleur en fonction du type de notification
 * @param {string} type - Type de notification
 * @returns {string} - Couleur hexadécimale
 */
function getNotificationColor(type) {
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        info: '#17a2b8',
        warning: '#ffc107'
    };
    return colors[type] || '#6c757d';
}

// ============================================
// Amélioration des formulaires
// ============================================

// Ajouter la classe 'focus' aux champs de formulaire
const formInputs = document.querySelectorAll('input, textarea, select');
formInputs.forEach(input => {
    input.addEventListener('focus', () => {
        input.parentElement.classList.add('focus');
    });
    
    input.addEventListener('blur', () => {
        input.parentElement.classList.remove('focus');
    });
});

// ============================================
// Gestion des boutons dans les cellules du tableau
// ============================================

// Empêcher la soumission multiple des formulaires
const cellForms = document.querySelectorAll('.cell-btn').forEach(btn => {
    const form = btn.closest('form');
    if (form) {
        let isSubmitting = false;
        
        form.addEventListener('submit', (e) => {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }
            
            isSubmitting = true;
            btn.disabled = true;
            btn.textContent = '...';
            
            // Réactiver après un court délai (pour éviter les doubles clics)
            setTimeout(() => {
                isSubmitting = false;
                btn.disabled = false;
            }, 1000);
        });
    }
});

// ============================================
// Confirmation avant suppression
// ============================================

// Ajouter une confirmation pour les liens de suppression
const deleteLinks = document.querySelectorAll('.btn-delete');
deleteLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.')) {
            e.preventDefault();
        }
    });
});

// ============================================
// Gestion du nom d'utilisateur
// ============================================

// Sauvegarder le nom d'utilisateur dans le localStorage
const userNameInput = document.querySelector('input[name="user_name"]');
if (userNameInput) {
    const savedName = localStorage.getItem('user_name');
    if (savedName) {
        userNameInput.value = savedName;
    }
    
    userNameInput.closest('form').addEventListener('submit', () => {
        localStorage.setItem('user_name', userNameInput.value);
    });
}

// ============================================
// Amélioration de l'interface admin
// ============================================

// Masquer/afficher le mot de passe dans le formulaire de gestion
const passwordToggle = document.createElement('style');
passwordToggle.textContent = `
    .password-container {
        position: relative;
    }
    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #666;
    }
`;
document.head.appendChild(passwordToggle);

const passwordInputs = document.querySelectorAll('input[type="password"]');
passwordInputs.forEach(input => {
    const container = document.createElement('div');
    container.className = 'password-container';
    
    const toggleBtn = document.createElement('button');
    toggleBtn.type = 'button';
    toggleBtn.className = 'password-toggle';
    toggleBtn.innerHTML = '👁️';
    toggleBtn.title = 'Afficher/Masquer le mot de passe';
    
    toggleBtn.addEventListener('click', () => {
        if (input.type === 'password') {
            input.type = 'text';
            toggleBtn.innerHTML = '🔒';
        } else {
            input.type = 'password';
            toggleBtn.innerHTML = '👁️';
        }
    });
    
    input.parentNode.insertBefore(container, input);
    container.appendChild(input);
    container.appendChild(toggleBtn);
});

// ============================================
// Gestion des onglets dans l'admin
// ============================================

// Activer l'onglet courant dans la navigation
const navLinks = document.querySelectorAll('.nav-link');
const currentPath = window.location.pathname;

navLinks.forEach(link => {
    if (currentPath.includes(link.getAttribute('href').replace(/\.\./g, ''))) {
        link.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
        link.style.fontWeight = 'bold';
    }
});

// ============================================
// Validation des formulaires côté client
// ============================================

// Validation du formulaire de création/modification de liste
const listForm = document.querySelector('form[action*="manage.php"]');
if (listForm) {
    listForm.addEventListener('submit', (e) => {
        const nameInput = listForm.querySelector('input[name="name"]');
        const columnsInput = listForm.querySelector('textarea[name="columns"]');
        
        if (!nameInput.value.trim()) {
            showNotification('Le nom de la liste est obligatoire.', 'error');
            nameInput.focus();
            e.preventDefault();
            return;
        }
        
        if (!columnsInput.value.trim()) {
            showNotification('Au moins une colonne est obligatoire.', 'error');
            columnsInput.focus();
            e.preventDefault();
            return;
        }
        
        // Vérifier que les colonnes sont séparées par des virgules
        const columns = columnsInput.value.split(',').map(c => c.trim()).filter(c => c);
        if (columns.length === 0) {
            showNotification('Veuillez entrer au moins une colonne valide.', 'error');
            columnsInput.focus();
            e.preventDefault();
            return;
        }
    });
}

// ============================================
// Amélioration de l'affichage des tableaux
// ============================================

// Alterner les couleurs des lignes des tableaux
const tableRows = document.querySelectorAll('.registration-table tbody tr, .admin-table tbody tr');
tableRows.forEach((row, index) => {
    if (index % 2 === 1 && !row.classList.contains('current-user-row')) {
        row.style.backgroundColor = 'rgba(0, 0, 0, 0.02)';
    }
});

// ============================================
// Gestion des messages dans l'URL
// ============================================

// Afficher les messages de succès/erreur depuis l'URL
const urlParams = new URLSearchParams(window.location.search);
const message = urlParams.get('message');
if (message) {
    // Déterminer le type de message
    let type = 'info';
    if (message.includes('succès') || message.includes('success')) {
        type = 'success';
    } else if (message.includes('erreur') || message.includes('error') || message.includes('incorrect')) {
        type = 'error';
    }
    
    showNotification(decodeURIComponent(message), type);
    
    // Supprimer le paramètre de l'URL
    urlParams.delete('message');
    window.history.replaceState({}, '', window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : ''));
}

// ============================================
// Initialisation
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Ajouter la classe 'loaded' au body pour les animations
    document.body.classList.add('loaded');
    
    // Initialiser les tooltips (si besoin)
    const elementsWithTitle = document.querySelectorAll('[title]');
    elementsWithTitle.forEach(el => {
        el.addEventListener('mouseenter', () => {
            // Simple tooltip (pourrait être amélioré)
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = el.title;
            tooltip.style.cssText = `
                position: absolute;
                background: #333;
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 4px;
                font-size: 0.875rem;
                z-index: 1000;
                white-space: nowrap;
                left: ${el.getBoundingClientRect().left + window.scrollX}px;
                top: ${el.getBoundingClientRect().top + window.scrollY - 40}px;
            `;
            document.body.appendChild(tooltip);
            
            el.addEventListener('mouseleave', () => {
                tooltip.remove();
            }, { once: true });
        });
    });
});

// ============================================
// Fonctions pour l'export/import
// ============================================

// Afficher un aperçu du fichier CSV avant import (optionnel)
const fileInput = document.querySelector('input[type="file"]');
if (fileInput) {
    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const fileInfo = document.createElement('div');
            fileInfo.className = 'file-info';
            fileInfo.innerHTML = `
                <p><strong>Fichier sélectionné :</strong> ${file.name}</p>
                <p><strong>Taille :</strong> ${formatFileSize(file.size)}</p>
            `;
            fileInfo.style.cssText = `
                margin-top: 1rem;
                padding: 1rem;
                background-color: #f8f9fa;
                border-radius: 4px;
                border: 1px solid #dee2e6;
            `;
            
            const parent = fileInput.parentElement;
            const existingInfo = parent.querySelector('.file-info');
            if (existingInfo) {
                existingInfo.remove();
            }
            parent.appendChild(fileInfo);
        }
    });
}

/**
 * Formater la taille d'un fichier
 * @param {number} bytes - Taille en octets
 * @returns {string} - Taille formatée
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 octets';
    const k = 1024;
    const sizes = ['octets', 'Ko', 'Mo', 'Go'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// ============================================
// Fin du script
// ============================================
