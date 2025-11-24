<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Admin — Gestión de Personal</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('frontend/Panel-admin/admin.css') }}" />

	<!-- ====== FAVICON / PWA ====== -->
		<link rel="icon" type="image/png" sizes="96x96" href="{{ asset('frontend/icons/favicon-96x96.png') }}">
		<link rel="icon" type="image/svg+xml" href="{{ asset('frontend/icons/favicon.svg') }}">
		<link rel="shortcut icon" href="{{ asset('frontend/icons/favicon.ico') }}">
		<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('frontend/icons/apple-touch-icon.png') }}">
		<meta name="apple-mobile-web-app-title" content="TCADM">
		<link rel="manifest" href="{{ asset('frontend/icons/site.webmanifest') }}">
</head>
<body data-page="personal">
	<header class="header">
		<div class="wrap">
			<div class="brand">
				<div class="brand-logo" aria-hidden="true"></div>
				<div>
					<div class="brand-title">Panel de Administración</div>
					<div class="brand-sub">Bienvenido, {{ Auth::user()->name ?? 'Admin' }}</div>
				</div>
			</div>
			<nav class="nav">
				<a href="{{ route('admin.dashboard') }}" data-nav="index">Asignar Mecánicos</a>
				<a href="{{ route('admin.servicios') }}" data-nav="servicios">Servicios</a>
				<a href="{{ route('admin.personal') }}" data-nav="personal">Gestión de Personal</a>
				<a href="{{ route('admin.reportes') }}" data-nav="reportes">Reportes</a>
				<a href="{{ route('admin.estadisticas') }}" data-nav="estadisticas">Estadísticas</a>
				<form method="POST" action="{{ route('logout') }}">
					@csrf
					<button type="submit" class="button ghost">Cerrar sesión</button>
				</form>
			</nav>
		</div>
	</header>

	<main class="main">
		@if(session('success'))
			<div style="background:#dcfce7;color:#065f46;padding:12px 16px;border-radius:8px;border:1px solid #86efac;">
				{{ session('success') }}
			</div>
		@endif

		@if($errors->any())
			<div style="background:#fee2e2;color:#991b1b;padding:12px 16px;border-radius:8px;border:1px solid #fecaca;">
				<strong>Revisa el formulario:</strong>
				<ul style="margin:8px 0 0 18px;">
					@foreach($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		<section class="section">
			<div class="section-head">
				<div>
					<h3 style="margin:0;font-size:16px">Gestión de Personal</h3>
					<p class="section-desc">Administra administradores, mecánicos y recepcionistas</p>
				</div>
				<button type="button" class="button primary" data-open="#modalPersonal">Asignar personal</button>
			</div>

			<div class="cards-3">
				@foreach(($personalRoles ?? []) as $meta)
					@php
						$slug = $meta['slug'];
						$totalRol = $personalTotals[$slug] ?? 0;
					@endphp
					<article class="card">
						<div class="head"><span class="title">{{ $meta['label'] }}</span></div>
						<div class="body">
							<div class="kpi">{{ number_format($totalRol) }}</div>
							<div class="sub">Personal activo</div>
						</div>
					</article>
				@endforeach
			</div>
		</section>

		<section class="section">
			<div class="section-head">
				<div>
					<h3 style="margin:0;font-size:16px">Personal registrado</h3>
					<p class="section-desc">Listado general con opciones de edición y baja</p>
				</div>
			</div>

			<div class="table cols-5">
				<header>
					<div>Nombre</div>
					<div>Rol</div>
					<div>Contacto</div>
					<div>Creado</div>
					<div class="text-right">Acciones</div>
				</header>

				@forelse(($personalList ?? collect()) as $persona)
					@php
						$rolNombre = $personalRoles[$persona->role_id]['label'] ?? 'Sin rol';
					@endphp
					<div class="row">
						<div>
							<div class="personal-name">{{ $persona->name }}</div>
							<div class="small muted">{{ $persona->email }}</div>
						</div>
						<div>{{ $rolNombre }}</div>
						<div>
							<div>{{ $persona->phone ?: 'Sin teléfono' }}</div>
						</div>
						<div>{{ optional($persona->created_at)->format('d/m/Y') }}</div>
						<div class="text-right" style="display:flex;gap:8px;justify-content:flex-end;">
							<button type="button"
								class="button"
								data-open="#modalEditPersonal"
								data-id="{{ $persona->id }}"
								data-name="{{ $persona->name }}"
								data-email="{{ $persona->email }}"
								data-phone="{{ $persona->phone ?? '' }}"
								data-role="{{ $persona->role_id }}">
								Editar
							</button>
							<form method="POST" action="{{ route('admin.personal.destroy', $persona) }}" onsubmit="return confirm('¿Eliminar a {{ $persona->name }}?');">
								@csrf
								@method('DELETE')
								<button type="submit" class="button danger">Eliminar</button>
							</form>
						</div>
					</div>
				@empty
					<div class="row" style="grid-column:1 / -1;text-align:center;color:#6b7280;">
						No hay personal registrado todavía.
					</div>
				@endforelse
			</div>

			@if(($personalList ?? null) && method_exists($personalList, 'hasPages') && $personalList->hasPages())
				<div style="margin-top:16px;display:flex;justify-content:center;">
					{{ $personalList->withQueryString()->links() }}
				</div>
			@endif
		</section>
	</main>

	<!-- Modal Registrar Personal -->
	<div class="modal-backdrop" id="modalPersonal">
		<div class="modal" role="dialog" aria-modal="true" aria-labelledby="personalTitle">
			<form method="POST" action="{{ route('admin.personal.store') }}">
				@csrf
				<input type="hidden" name="form_origin" value="create">
				<div class="m-head">
					<div id="personalTitle" class="m-title">Asignar personal</div>
					<div class="m-sub">Crea cuentas internas para tu equipo</div>
				</div>
				<div class="m-body">
					<div class="grid-2">
						<div>
							<label class="label" for="personalName">Nombre completo</label>
							<input type="text" id="personalName" name="name" class="input" value="{{ old('name') }}" required>
						</div>
						<div>
							<label class="label" for="personalRole">Rol</label>
							<select id="personalRole" name="role_id" class="select" required>
								<option value="">Selecciona un rol</option>
								@foreach(($personalRoles ?? []) as $roleId => $meta)
									<option value="{{ $roleId }}" @selected(old('role_id') == $roleId)>{{ $meta['label'] }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div>
						<label class="label" for="personalEmail">Correo electrónico</label>
						<input type="email" id="personalEmail" name="email" class="input" value="{{ old('email') }}" required>
					</div>
					<div>
						<label class="label" for="personalPhone">Teléfono de contacto</label>
						<input type="tel" id="personalPhone" name="phone" class="input" value="{{ old('phone') }}" placeholder="Ej. 55 1234 5678">
					</div>
					<div>
						<label class="label" for="personalPassword">Contraseña temporal</label>
						<input type="password" id="personalPassword" name="password" class="input" minlength="8" required>
					</div>
				</div>
				<div class="m-footer">
					<button type="button" class="button" data-close>Cancelar</button>
					<button type="submit" class="button primary">Guardar</button>
				</div>
			</form>
		</div>
	</div>

	<!-- Modal Editar Personal -->
	<div class="modal-backdrop" id="modalEditPersonal">
		<div class="modal" role="dialog" aria-modal="true" aria-labelledby="editPersonalTitle">
			<form method="POST" id="formEditPersonal" data-action-template="{{ url('/admin/personal') }}/__ID__">
				@csrf
				@method('PUT')
				<input type="hidden" name="form_origin" value="edit">
				<input type="hidden" id="editPersonalId" name="editing_user_id" value="{{ old('form_origin') === 'edit' ? old('editing_user_id') : '' }}">
				<div class="m-head">
					<div id="editPersonalTitle" class="m-title">Editar personal</div>
					<div class="m-sub">Actualiza la información del miembro seleccionado</div>
				</div>
				<div class="m-body">
					<div class="grid-2">
						<div>
							<label class="label" for="editPersonalName">Nombre completo</label>
							<input type="text" id="editPersonalName" name="name" class="input" value="{{ old('form_origin') === 'edit' ? old('name') : '' }}" required>
						</div>
						<div>
							<label class="label" for="editPersonalRole">Rol</label>
							<select id="editPersonalRole" name="role_id" class="select" required>
								<option value="">Selecciona un rol</option>
								@foreach(($personalRoles ?? []) as $roleId => $meta)
									<option value="{{ $roleId }}" @selected(old('form_origin') === 'edit' && (int) old('role_id') === $roleId)>{{ $meta['label'] }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div>
						<label class="label" for="editPersonalEmail">Correo electrónico</label>
							<input type="email" id="editPersonalEmail" name="email" class="input" value="{{ old('form_origin') === 'edit' ? old('email') : '' }}" required>
					</div>
					<div>
						<label class="label" for="editPersonalPhone">Teléfono de contacto</label>
							<input type="tel" id="editPersonalPhone" name="phone" class="input" value="{{ old('form_origin') === 'edit' ? old('phone') : '' }}" placeholder="Ej. 55 1234 5678">
					</div>
					<div>
						<label class="label" for="editPersonalPassword">Nueva contraseña (opcional)</label>
						<input type="password" id="editPersonalPassword" name="password" class="input" minlength="8" placeholder="Déjala vacía para conservar la actual">
					</div>
				</div>
				<div class="m-footer">
					<button type="button" class="button" data-close>Cancelar</button>
					<button type="submit" class="button primary">Guardar cambios</button>
				</div>
			</form>
		</div>
	</div>

	<script src="{{ asset('frontend/Panel-admin/admin.js') }}"></script>
	<script>
		document.addEventListener('DOMContentLoaded', () => {
			const editForm = document.getElementById('formEditPersonal');
			const actionTemplate = editForm ? editForm.dataset.actionTemplate : '';
			const editIdInput = document.getElementById('editPersonalId');
			if (editForm && actionTemplate) {
				document.querySelectorAll('[data-open="#modalEditPersonal"]').forEach(btn => {
					btn.addEventListener('click', () => {
						const id = btn.dataset.id;
						if (!id) return;
						editForm.action = actionTemplate.replace('__ID__', id);
						if (editIdInput) editIdInput.value = id;
						document.getElementById('editPersonalName').value = btn.dataset.name || '';
						document.getElementById('editPersonalEmail').value = btn.dataset.email || '';
						document.getElementById('editPersonalPhone').value = btn.dataset.phone || '';
						document.getElementById('editPersonalRole').value = btn.dataset.role || '';
						document.getElementById('editPersonalPassword').value = '';
					});
				});
			}
		});
	</script>

	@php
		$oldFormOrigin = old('form_origin');
		$oldEditingId = old('form_origin') === 'edit' ? old('editing_user_id') : null;
	@endphp

	@if($oldFormOrigin === 'create')
		<script>
			document.addEventListener('DOMContentLoaded', () => {
				const modal = document.getElementById('modalPersonal');
				if (modal) modal.style.display = 'grid';
			});
		</script>
	@elseif($oldFormOrigin === 'edit' && $oldEditingId)
		<script>
			document.addEventListener('DOMContentLoaded', () => {
				const form = document.getElementById('formEditPersonal');
				const template = form ? form.dataset.actionTemplate : '';
				if (form && template) {
					form.action = template.replace('__ID__', '{{ $oldEditingId }}');
				}
				const idInput = document.getElementById('editPersonalId');
				if (idInput) idInput.value = '{{ $oldEditingId }}';
				const modal = document.getElementById('modalEditPersonal');
				if (modal) modal.style.display = 'grid';
			});
		</script>
	@endif
</body>
</html>
