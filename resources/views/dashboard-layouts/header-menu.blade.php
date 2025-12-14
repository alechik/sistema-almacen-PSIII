<!--begin::Header-->
<nav class="app-header navbar navbar-expand bg-body">
<!--begin::Container-->
<div class="container-fluid">
    <!--begin::Start Navbar Links-->
    <ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
        <i class="bi bi-list"></i>
        </a>
    </li>
    {{-- <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Home</a></li>
    <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Contact</a></li> --}}
    </ul>
    <!--end::Start Navbar Links-->
    <!--begin::End Navbar Links-->
    <ul class="navbar-nav ms-auto">
    <!--begin::Navbar Search-->
    <li class="nav-item" hidden>
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
        <i class="bi bi-search"></i>
        </a>
    </li>
    <!--end::Navbar Search-->

    <!--begin::Notifications Dropdown Menu-->
    @if (auth()->user()->hasAnyRole(['admin']))
        <li class="nav-item dropdown">

            <a class="nav-link" data-bs-toggle="dropdown" href="#">
                <i class="bi bi-bell-fill"></i>

                @if(isset($cantidadPendientes) && $cantidadPendientes > 0)
                    <span class="navbar-badge badge text-bg-danger">
                        {{ $cantidadPendientes }}
                    </span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">

                @if(isset($cantidadPendientes) && $cantidadPendientes > 0)

                    <span class="dropdown-item dropdown-header">
                        {{ $cantidadPendientes }} Usuarios Pendientes
                    </span>

                    <div class="dropdown-divider"></div>

                    @foreach ($pendientesUsuarios as $u)
                        <a href="{{ route('users.edit', $u->id) }}" class="dropdown-item">

                            <i class="bi bi-person-plus-fill me-2"></i>
                            <strong>{{ $u->full_name }}</strong>
                            <br>

                            <span class="text-muted fs-7">{{ $u->email }}</span>

                            <span class="float-end text-secondary fs-7">
                                {{ $u->created_at->diffForHumans() }}
                            </span>
                        </a>
                        <div class="dropdown-divider"></div>
                    @endforeach

                    <a href="{{ route('users.index') }}" class="dropdown-item dropdown-footer">
                        Ver todos los usuarios
                    </a>

                @else

                    <span class="dropdown-item text-center text-muted p-3">
                        No hay usuarios pendientes
                    </span>

                @endif

            </div>

        </li>
    @endif
    @role('propietario')
        <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#">
                <i class="bi bi-bell-fill"></i>
                @if($cantidadPedidosPendientes > 0)
                    <span class="badge bg-warning navbar-badge">
                        {{ $cantidadPedidosPendientes }}
                    </span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <span class="dropdown-header">
                    {{ $cantidadPedidosPendientes }} pedidos pendientes
                </span>

                @foreach($pedidosPendientes as $pedido)
                    <a href="{{ route('pedidos.show', $pedido->id) }}" class="dropdown-item">
                        <i class="fas fa-box me-2"></i> Pedido #{{ $pedido->id }}
                        <span class="float-end text-muted text-sm">{{ $pedido->created_at->diffForHumans() }}</span>
                    </a>
                @endforeach

                <div class="dropdown-divider"></div>
                <a href="{{ route('pedidos.index') }}" class="dropdown-item dropdown-footer">
                    Ver todos los pedidos
                </a>
            </div>
        </li>
    @endrole
    @if(auth()->user()->hasRole('administrador'))
        <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#">
                <i class="bi bi-bell"></i>
                @if($cantidadStockMinimo > 0)
                    <span class="navbar-badge badge text-bg-warning">
                        {{ $cantidadStockMinimo }}
                    </span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <span class="dropdown-header">
                    Productos con stock mínimo
                </span>

                @forelse($productosStockMinimo as $producto)
                    <a href="{{ route('stock-minimo.index') }}" class="dropdown-item">
                        <i class="bi bi-box-seam me-2"></i>
                        {{ $producto->nombre }}
                        <span class="float-end text-muted fs-7">
                            {{ $producto->almacenes->first()->pivot->stock }}
                        </span>
                    </a>
                @empty
                    <span class="dropdown-item text-muted">
                        No hay alertas
                    </span>
                @endforelse
            </div>
        </li>
    @endif
    <!--end::Notifications Dropdown Menu-->

    
    <!--begin::Fullscreen Toggle-->
    <li class="nav-item">
        <a class="nav-link" href="#" data-lte-toggle="fullscreen">
        <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
        <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
        </a>
    </li>
    <!--end::Fullscreen Toggle-->
    <!--begin::User Menu Dropdown-->
    <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
        <img
            src="{{asset('assets/img/user2-160x160.jpg')}}"
            class="user-image rounded-circle shadow"
            alt="User Image"
        />
        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
        <!--begin::User Image-->
        <li class="user-header text-bg-primary">
            <img
            src="{{asset('assets/img/user2-160x160.jpg')}}"
            class="rounded-circle shadow"
            alt="User Image"
            />
            <p>
            {{ Auth::user()->name }}
            <small>Member since {{ Auth::user()->created_at->format('d M. Y') }}</small>
            </p>
        </li>
        <!--end::User Image-->
        <!--begin::Menu Body-->
        <li class="user-body" hidden>
            <!--begin::Row-->
            <div class="row">
            <div class="col-4 text-center"><a href="#">Followers</a></div>
            <div class="col-4 text-center"><a href="#">Sales</a></div>
            <div class="col-4 text-center"><a href="#">Friends</a></div>
            </div>
            <!--end::Row-->
        </li>
        <!--end::Menu Body-->
        <!--begin::Menu Footer-->
        <li class="user-footer">
            <a href="{{ route('users.edittwo', Auth::user()->id) }}" class="btn btn-default btn-flat">Perfil</a>
            <!-- Botón de Logout -->
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-default btn-flat float-end">
                    Cerrar Sesión
                </button>
            </form>
        </li>
        <!--end::Menu Footer-->
        </ul>
    </li>
    <!--end::User Menu Dropdown-->
    </ul>
    <!--end::End Navbar Links-->
</div>
<!--end::Container-->
</nav>
<!--end::Header-->