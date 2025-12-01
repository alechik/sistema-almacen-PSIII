  {{-- MENU --}}
  <!--begin::Sidebar-->
  <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!--begin::Sidebar Brand-->
    <div class="sidebar-brand">
      <!--begin::Brand Link-->
      <a href="./index.html" class="brand-link">
        <!--begin::Brand Image-->
        <img
          src="{{asset('assets/img/AdminLTELogo.png')}}"
          alt="AdminLTE Logo"
          class="brand-image opacity-75 shadow"
        />
        <!--end::Brand Image-->
        <!--begin::Brand Text-->
        <span class="brand-text fw-light">Sis. Almacen</span>
        <!--end::Brand Text-->
      </a>
      <!--end::Brand Link-->
    </div>
    <!--end::Sidebar Brand-->
    <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
      <nav class="mt-2">
        <!--begin::Sidebar Menu-->
        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation" data-accordion="false"
          id="navigation" >
          <li class="nav-item {{ request()->routeIs('almacenes.*','tiposalidas.*','tipoingresos.*','categorias.*','vehiculos.*','productos.*' ,'pedidos.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('almacenes.*','tiposalidas.*','tipoingresos.*','categorias.*','vehiculos.*','productos.*','pedidos.*') ? 'active' : '' }}">
              <i class="nav-icon bi bi-box-seam-fill"></i>
              <p>
                ADM. DE ALMACENES
                <i class="nav-arrow bi bi-chevron-right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('almacenes.index')}}" class="nav-link {{ request()->routeIs('almacenes.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-circle"></i>
                  <p>ALMACEN</p>
                </a>
              </li>
              <li class="nav-item {{ request()->routeIs('tiposalidas.*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle"></i>
                  <p>
                    GESTION SALIDAS
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-dot"></i>
                      <p>Salidas</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('tiposalidas.index')}}" class="nav-link {{ request()->routeIs('tiposalidas.*') ? 'active' : '' }}">
                      <i class="nav-icon bi bi-dot"></i>
                      <p>Tipo de Salidas</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item {{ request()->routeIs('tipoingresos.*','pedidos.*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle"></i>
                  <p>
                    GESTION INGRESOS
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('pedidos.create')}}" class="nav-link {{ request()->routeIs('pedidos.create') ? 'active' : '' }}">
                      <i class="nav-icon bi bi-dot"></i>
                      <p>NUEVO PEDIDO</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('pedidos.index')}}" class="nav-link {{ request()->routeIs('pedidos.index','pedidos.edit','pedidos.show') ? 'active' : '' }}">
                      <i class="nav-icon bi bi-dot"></i>
                      <p>CONSULTAR PEDIDOS</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-dot"></i>
                      <p>NUEVO INGRESO</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-dot"></i>
                      <p>CONSULTAR INGRESOS</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('tipoingresos.index')}}" class="nav-link {{ request()->routeIs('tipoingresos.*') ? 'active' : '' }}">
                      <i class="nav-icon bi bi-dot"></i>
                      <p>Tipo de Ingresos</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item {{ request()->routeIs('categorias.*','productos.*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle"></i>
                  <p>
                    GESTION ARTICULOS
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('productos.index')}}" class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}">
                      <i class="nav-icon bi bi-dot"></i>
                      <p>Productos</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('categorias.index')}}" class="nav-link {{ request()->routeIs('categorias.*') ? 'active' : '' }}">
                      <i class="nav-icon bi bi-dot"></i>
                      <p>Categorias</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="{{route('vehiculos.index')}}" class="nav-link {{ request()->routeIs('vehiculos.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-circle"></i>
                  <p>VEHICULOS</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item {{ request()->routeIs('users.*','roles.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('users.*','roles.*') ? 'active' : '' }}">
              <i class="nav-icon bi bi-box-seam-fill"></i>
              <p>
                ADM. GENERAL
                <i class="nav-arrow bi bi-chevron-right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              {{-- <li class="nav-item">
                <a href="{{route('usuarios.index')}}" class="nav-link {{ request()->routeIs('almacenes.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-circle"></i>
                  <p>ALMACEN</p>
                </a>
              </li> --}}
              <li class="nav-item {{ request()->routeIs('users.*','roles.*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle"></i>
                  <p>
                    GESTION USUARIOS
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('users.index')}}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                      <i class="nav-icon bi bi-dot"></i>
                      <p>Usuarios</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('roles.index')}}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                      <i class="nav-icon bi bi-dot"></i>
                      <p>Roles</p>
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </li>
        </ul>
        <!--end::Sidebar Menu-->
      </nav>
    </div>
    <!--end::Sidebar Wrapper-->
  </aside>
  <!--end::Sidebar-->
  {{-- FIN MENU --}}