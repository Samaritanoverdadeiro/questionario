document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            // Validação básica no frontend
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            
            // Validação básica
            if (!email || !senha) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos.');
                return;
            }
            
            // Validação de formato de email
            if (!validarEmail(email)) {
                e.preventDefault();
                alert('Por favor, insira um e-mail válido.');
                return;
            }
            
            // Se passou nas validações, o formulário será enviado
            // A autenticação real acontece no servidor (autenticar.php)
        });
    }
    
    // Função para validar formato de e-mail
    function validarEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
});


// Função para alternar entre abas
function showTab(tabName, buttonElement) {
    // Atualizar parâmetro na URL sem recarregar a página
    const url = new URL(window.location);
    url.searchParams.set('aba', tabName);
    window.history.replaceState({}, '', url);

    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    // Show selected tab
    document.getElementById(tabName).classList.add('active');

    // Update active button
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    buttonElement.classList.add('active');

    // Atualizar todos os hidden fields de aba ativa nos formulários
    document.querySelectorAll('input[name="aba_ativa"]').forEach(input => {
        input.value = tabName;
    });
}

// Função para destacar texto
function highlightText(element, searchTerm) {
    const text = element.textContent;
    const regex = new RegExp(`(${searchTerm})`, 'gi');
    element.innerHTML = text.replace(regex, '<span class="highlight">$1</span>');
}

// Funções para Instituições
function filterInstituicoes(searchTerm) {
    const rows = document.querySelectorAll('#instituicoes table tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const nomeCell = row.querySelector('td:nth-child(2)'); // Coluna do nome
        const nome = nomeCell.textContent.toLowerCase();
        
        // Remover highlights anteriores
        nomeCell.innerHTML = nomeCell.textContent;
        
        if (searchTerm === '' || nome.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
            
            // Destacar o texto encontrado
            if (searchTerm !== '') {
                highlightText(nomeCell, searchTerm);
            }
        } else {
            row.style.display = 'none';
        }
    });
    
    updateResultCountInstituicoes(visibleCount, rows.length);
}

function updateResultCountInstituicoes(visible, total) {
    const resultCount = document.getElementById('resultCountInstituicoes');
    if (resultCount) {
        if (visible === total) {
            resultCount.textContent = `${total} instituições`;
        } else {
            resultCount.textContent = `${visible} de ${total} instituições`;
        }
    }
}

function clearSearchInstituicoes() {
    const searchInput = document.getElementById('searchInstituicoes');
    searchInput.value = '';
    filterInstituicoes('');
    searchInput.parentNode.querySelector('.clear-btn').style.display = 'none';
}

// Funções para Alunos
function filterAlunos(searchTerm) {
    const rows = document.querySelectorAll('#alunos table tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const nomeCell = row.querySelector('td:nth-child(2)'); // Coluna do nome
        const nome = nomeCell.textContent.toLowerCase();
        
        // Remover highlights anteriores
        nomeCell.innerHTML = nomeCell.textContent;
        
        if (searchTerm === '' || nome.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
            
            // Destacar o texto encontrado
            if (searchTerm !== '') {
                highlightText(nomeCell, searchTerm);
            }
        } else {
            row.style.display = 'none';
        }
    });
    
    updateResultCountAlunos(visibleCount, rows.length);
}

function updateResultCountAlunos(visible, total) {
    const resultCount = document.getElementById('resultCountAlunos');
    if (resultCount) {
        if (visible === total) {
            resultCount.textContent = `${total} alunos`;
        } else {
            resultCount.textContent = `${visible} de ${total} alunos`;
        }
    }
}

function clearSearchAlunos() {
    const searchInput = document.getElementById('searchAlunos');
    searchInput.value = '';
    filterAlunos('');
    searchInput.parentNode.querySelector('.clear-btn').style.display = 'none';
}

// Funções para Professores
function filterProfessores(searchTerm) {
    const rows = document.querySelectorAll('#professores table tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const nomeCell = row.querySelector('td:nth-child(2)'); // Coluna do nome
        const nome = nomeCell.textContent.toLowerCase();
        
        // Remover highlights anteriores
        nomeCell.innerHTML = nomeCell.textContent;
        
        if (searchTerm === '' || nome.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
            
            // Destacar o texto encontrado
            if (searchTerm !== '') {
                highlightText(nomeCell, searchTerm);
            }
        } else {
            row.style.display = 'none';
        }
    });
    
    updateResultCountProfessores(visibleCount, rows.length);
}

function updateResultCountProfessores(visible, total) {
    const resultCount = document.getElementById('resultCountProfessores');
    if (resultCount) {
        if (visible === total) {
            resultCount.textContent = `${total} professores`;
        } else {
            resultCount.textContent = `${visible} de ${total} professores`;
        }
    }
}

function clearSearchProfessores() {
    const searchInput = document.getElementById('searchProfessores');
    searchInput.value = '';
    filterProfessores('');
    searchInput.parentNode.querySelector('.clear-btn').style.display = 'none';
}

// Inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Restaurar aba da URL ao carregar a página
    const urlParams = new URLSearchParams(window.location.search);
    const abaUrl = urlParams.get('aba');

    if (abaUrl) {
        const button = document.querySelector(`.view-btn[onclick*="${abaUrl}"]`);
        if (button) {
            showTab(abaUrl, button);
        }
    }

    // Inicializar pesquisa de instituições
    const searchInstituicoes = document.getElementById('searchInstituicoes');
    if (searchInstituicoes) {
        const clearBtnInstituicoes = searchInstituicoes.parentNode.querySelector('.clear-btn');
        const totalInstituicoes = document.querySelectorAll('#instituicoes table tbody tr').length;
        
        updateResultCountInstituicoes(totalInstituicoes, totalInstituicoes);
        
        searchInstituicoes.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            filterInstituicoes(searchTerm);
            clearBtnInstituicoes.style.display = searchTerm ? 'block' : 'none';
        });
    }

    // Inicializar pesquisa de alunos
    const searchAlunos = document.getElementById('searchAlunos');
    if (searchAlunos) {
        const clearBtnAlunos = searchAlunos.parentNode.querySelector('.clear-btn');
        const totalAlunos = document.querySelectorAll('#alunos table tbody tr').length;
        
        updateResultCountAlunos(totalAlunos, totalAlunos);
        
        searchAlunos.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            filterAlunos(searchTerm);
            clearBtnAlunos.style.display = searchTerm ? 'block' : 'none';
        });
    }

    // Inicializar pesquisa de professores
    const searchProfessores = document.getElementById('searchProfessores');
    if (searchProfessores) {
        const clearBtnProfessores = searchProfessores.parentNode.querySelector('.clear-btn');
        const totalProfessores = document.querySelectorAll('#professores table tbody tr').length;
        
        updateResultCountProfessores(totalProfessores, totalProfessores);
        
        searchProfessores.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            filterProfessores(searchTerm);
            clearBtnProfessores.style.display = searchTerm ? 'block' : 'none';
        });
    }

    // Prevenir submit com Enter nas pesquisas
    document.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && e.target.type === 'text' && 
            (e.target.id.includes('searchInstituicoes') || 
             e.target.id.includes('searchAlunos') || 
             e.target.id.includes('searchProfessores'))) {
            e.preventDefault();
        }
    });
});