const formularioPerfil = document.getElementById('editarDadosUser');
const formularioRegistrar = document.getElementById('cadastro');
const cepUserInput = document.getElementById('userCEP');
const spinnerCEP = document.getElementById('spinnerCep');

cepUserInput.addEventListener('blur', function () {
    const cepFormatado = cepUserInput.value.replace(/\D/g, '');
    const tamanhoCEP = 8;

    if (cepFormatado.length !== tamanhoCEP) return;

    // Mostra o spinner
    spinnerCEP.classList.remove('d-none');
    fetch(`https://viacep.com.br/ws/${cepFormatado}/json`)
    .then(res => res.json())
    .then(data => {
        if (data.erro) {
            alert('CEP não encontrado!');
            return;
        }

        document.getElementById('userAddress').value = data.logradouro || '';
        document.getElementById('userCity').value = data.localidade || '';
    })
    .catch(() => alert('Erro ao buscar o CEP'))
    .finally(() => {
        spinnerCEP.classList.add('d-none');
    });
});

// Validação do formulário 'editarDadosUser'
formularioPerfil.addEventListener('submit', function (e) {
    e.preventDefault();

    if (!formularioPerfil.checkValidity()) {
        formularioPerfil.classList.add('was-validated');
    }
    alert("Formulário enviado com sucesso!");
    formularioPerfil.reset();
    formularioPerfil.classList.remove('was-validated');
});

// Validação do formulário 'cadastro'
formularioRegistrar.addEventListener('submit', function (e) {
    e.preventDefault();

    if (!formularioRegistrar.checkValidity()) {
        formularioRegistrar.classList.add('was-validated');
    }
    alert("Formulário enviado com sucesso!");
    formularioRegistrar.reset();
    formularioRegistrar.classList.remove('was-validated');
});