<!DOCTYPE html>
<html lang="pt_br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="">
    <meta name="author" content="samoel duarte">
    <title>Menu Superior</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />
    <link href="{{ asset('/assets/admin/css/layout.css') }}" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#">Artica</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <!-- Menu Item with Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Cadastro
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">

                            @can('gerenciar usuários')
                                <li>
                                    <!-- Verifica se o usuário tem a permissão 'gerenciar usuários' -->
                                    <a class="dropdown-item" href="{{ route('usuario.index') }}">Usuários</a>
                                </li>
                            @endcan
                            @can('gerenciar clientes')
                                <li>
                                    <a class="dropdown-item" href="{{ route('cliente.index') }}">Clientes</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                    @can('gerenciar dispositivos')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">Dispositivos</a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li>
                                    <!-- Verifica se o usuário tem a permissão 'gerenciar usuários' -->
                                    <a class="dropdown-item" href="{{ route('dispositivo.index') }}">Dispositivo</a>
                                </li>
                            </ul>
                        </li>
                    @endcan

                   
                    <!-- User Profile with Dropdown, stays to the right -->
                    <li class="nav-item dropdown ms-auto user-info">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownProfile" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            {{ Auth::user()->email }} <!-- Exibe o nome ou email -->
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownProfile">
                            <li><a class="dropdown-item" href="#">Alterar Senha</a></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" style="display: none;"
                                    id="logoutForm">
                                    @csrf
                                </form>
                                <a class="dropdown-item" href="#"
                                    onclick="document.getElementById('logoutForm').submit();">Sair</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content" class="flex-grow-1">
            <!-- Begin Page Content -->
            <div class="container-fluid">

                @yield('content')
            </div>
        </div>
        <!-- End of Main Content -->

        <!-- Footer -->
        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <a href="https://betasolucao.com.br" target="_blank">
                        <span>Copyright &copy; Beta Solução {{ now()->year }}</span>
                    </a>
                </div>
            </div>
        </footer>
        <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->

    <!-- Scripts do Bootstrap -->
    <script src="{{ asset('/assets/admin/vendor/jquery/jquery.min.js') }} "></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('/assets/admin/js/global.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.money').mask('R$ 000.000.000,00', {
                reverse: true
            });
        });
        // Fechar dropdown ao clicar fora
        document.addEventListener('click', function(e) {
            const dropdowns = document.querySelectorAll('.dropdown-menu');
            dropdowns.forEach(function(dropdown) {
                if (!dropdown.contains(e.target) && !e.target.classList.contains('dropdown-toggle')) {
                    const dropdownToggle = dropdown.previousElementSibling;
                    const dropdownInstance = bootstrap.Dropdown.getInstance(dropdownToggle);
                    if (dropdownInstance) {
                        dropdownInstance.hide();
                    }
                }
            });
        });
    </script>


    @yield('scripts')
    @if (session('success'))
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            })

            Toast.fire({
                icon: 'success',
                title: "{!! session('success') !!}",
            })
        </script>
    @endif

    @if (session('error'))
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            })

            Toast.fire({
                icon: 'error',
                title: "{!! session('error') !!}",
            })
        </script>
    @endif

    @if ($errors->any())
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 8000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: 'error',
                title: `{!! implode('<br>', $errors->all()) !!}`
            });
        </script>
    @endif
    <script>
        function showToast(icon, message) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: icon,
                title: message
            });
        }
    </script>

</body>

</html>
