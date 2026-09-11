<div class="space-y-8">
    <div>
        <flux:heading size="xl">Hola, {{ auth()->user()->usu_nombre }}</flux:heading>
        <flux:text class="mt-2">Planifica tu visita a pie desde las estaciones de tren.</flux:text>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-tarjeta-modulo
            icono="heart"
            titulo="Mis preferencias"
            descripcion="Elige las categorías que te interesan: naturaleza, historia, aventura o gastronomía."
            :href="route('turista.preferencias')"
        />

        <x-tarjeta-modulo
            icono="map"
            titulo="Planificar mi visita"
            descripcion="Selecciona una estación y descubre las zonas a las que puedes llegar a pie, con el clima y los trenes."
            :href="route('turista.planificar')"
        />

        <x-tarjeta-modulo
            icono="document-text"
            titulo="Mis informes"
            descripcion="Revisa y descarga los informes que generaste."
            :href="route('turista.informes')"
        />
    </div>
</div>
