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
          @if (auth()->user()->hasAnyRole(['administrador', 'propietario']))
            <li class="nav-item {{ request()->routeIs('almacenes.*','tiposalidas.*','tipoingresos.*','categorias.*','vehiculos.*','productos.*' ,'pedidos.*','unidad-medidas.*','ingresos.*','salidas.*') ? 'menu-open' : '' }}">
              <a href="#" class="nav-link {{ request()->routeIs('almacenes.*','tiposalidas.*','tipoingresos.*','categorias.*','vehiculos.*','productos.*','pedidos.*','unidad-medidas.*','ingresos.*','salidas.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-box-seam-fill"></i>
                <p>
                  ADM. DE ALMACENES
                  <i class="nav-arrow bi bi-chevron-right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="{{route('almacenes.index')}}" class="nav-link {{ request()->routeIs('almacenes.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-building"></i>
                    <p>ALMACEN</p>
                  </a>
                </li>
                <li class="nav-item {{ request()->routeIs('tiposalidas.*') ? 'menu-open' : '' }}">
                  <a href="#" class="nav-link">
                    <i class="nav-icon bi bi-truck"></i>
                    <p>
                      GESTION SALIDAS
                      <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    @if (auth()->user()->hasAnyRole(['administrador']))
                      <li class="nav-item">
                        <a href="{{route('salidas.create')}}" class="nav-link {{ request()->routeIs('salidas.create') ? 'active' : '' }}">
                          <i class="nav-icon bi bi-plus-circle"></i>
                          <p>NUEVA SALIDA</p>
                        </a>
                      </li>
                    @endif
                    <li class="nav-item">
                      <a href="{{route('salidas.index')}}" class="nav-link {{ request()->routeIs('salidas.index','salidas.edit','salidas.show') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-search"></i>
                        <p>CONSULTAR SALIDAS</p>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{route('tiposalidas.index')}}" class="nav-link {{ request()->routeIs('tiposalidas.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-list-ul"></i>
                        <p>Tipo de Salidas</p>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item {{ request()->routeIs('tipoingresos.*','pedidos.*','ingresos.*') ? 'menu-open' : '' }}">
                  <a href="#" class="nav-link">
                    <i class="nav-icon bi bi-box-arrow-in-down"></i>
                    <p>
                      GESTION INGRESOS
                      <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    @if (auth()->user()->hasAnyRole(['administrador']))
                      <li class="nav-item">
                        <a href="{{route('pedidos.create')}}" class="nav-link {{ request()->routeIs('pedidos.create') ? 'active' : '' }}">
                          <i class="nav-icon bi bi-file-earmark-plus"></i>
                          <p>NUEVO PEDIDO</p>
                        </a>
                      </li>
                    @endif
                    <li class="nav-item">
                      <a href="{{route('pedidos.index')}}" class="nav-link {{ request()->routeIs('pedidos.index','pedidos.edit','pedidos.show') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-file-earmark-text"></i>
                        <p>CONSULTAR PEDIDOS</p>
                      </a>
                    </li>
                    @if (auth()->user()->hasAnyRole(['administrador']))
                      <li class="nav-item">
                        <a href="{{route('ingresos.create')}}" class="nav-link {{ request()->routeIs('ingresos.create') ? 'active' : '' }}">
                          <i class="nav-icon bi bi-box-arrow-in-down"></i>
                          <p>NUEVO INGRESO</p>
                        </a>
                      </li>
                    @endif
                    <li class="nav-item">
                      <a href="{{route('ingresos.index')}}" class="nav-link">
                        <i class="nav-icon bi bi-journals"></i>
                        <p>CONSULTAR INGRESOS</p>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{route('tipoingresos.index')}}" class="nav-link {{ request()->routeIs('tipoingresos.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-list-check"></i>
                        <p>Tipo de Ingresos</p>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item {{ request()->routeIs('categorias.*','productos.*','unidad-medidas.*') ? 'menu-open' : '' }}">
                  <a href="#" class="nav-link">
                    <i class="nav-icon bi bi-tags"></i>
                    <p>
                      GESTION ARTICULOS
                      <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    <li class="nav-item">
                      <a href="{{route('productos.index')}}" class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-bag-check"></i>
                        <p>Productos</p>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{route('categorias.index')}}" class="nav-link {{ request()->routeIs('categorias.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-grid-3x3-gap-fill"></i>
                        <p>Categorias</p>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{route('unidad-medidas.index')}}" class="nav-link {{ request()->routeIs('unidad-medidas.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-rulers"></i>
                        <p>Unidad Medidas</p>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item">
                  <a href="{{route('vehiculos.index')}}" class="nav-link {{ request()->routeIs('vehiculos.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-truck-front-fill"></i>
                    <p>VEHICULOS</p>
                  </a>
                </li>
              </ul>
            </li>
          @endif
          @if (auth()->user()->hasAnyRole(['admin', 'propietario','administrador', 'operador', 'transportista']))
            <li class="nav-item {{ request()->routeIs('users.*','roles.*') ? 'menu-open' : '' }}">
              <a href="#" class="nav-link {{ request()->routeIs('users.*','roles.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-box-seam-fill"></i>
                <p>
                  ADM. GENERAL
                  <i class="nav-arrow bi bi-chevron-right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item {{ request()->routeIs('users.*','roles.*') ? 'menu-open' : '' }}">
                  <a href="#" class="nav-link">
                    <i class="nav-icon bi bi-gear-fill"></i>
                    <p>
                      GESTION USUARIOS
                      <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    <li class="nav-item">
                      <a href="{{route('users.index')}}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-people-fill"></i>
                        <p>Usuarios</p>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{route('roles.index')}}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-shield-lock-fill"></i>
                        <p>Roles</p>
                      </a>
                    </li>
                  </ul>
                </li>
              </ul>
            </li>
          @endif
          @if (auth()->user()->hasAnyRole(['administrador', 'propietario']))
            <li class="nav-item {{ request()->routeIs('reportes.*') ? 'menu-open' : '' }}">
              <a href="#" class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-box-seam-fill"></i>
                <p>
                  REPORTES
                  <i class="nav-arrow bi bi-chevron-right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item {{ request()->routeIs('reportes.*') ? 'menu-open' : '' }}">
                  <a href="#" class="nav-link">
                    <i class="nav-icon bi bi-file-bar-graph"></i>
                    <p>
                      INFORMES DE ALMACEN
                      <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    <li class="nav-item">
                      <a href="{{route('reportes.salidas')}}" class="nav-link {{ request()->routeIs('reportes.salidas') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-file-arrow-up"></i>
                        <p>Reporte de Salidas</p>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{route('reportes.ingresos')}}" class="nav-link {{ request()->routeIs('reportes.ingresos') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-file-arrow-down"></i>
                        <p>Reporte de ingresos</p>
                      </a>
                    </li>
                  </ul>
                </li>
              </ul>
            </li>
          @endif
        </ul>
        <!--end::Sidebar Menu-->
      </nav>
    </div>
    <!--end::Sidebar Wrapper-->
  </aside>
  <!--end::Sidebar-->
  {{-- FIN MENU --}}