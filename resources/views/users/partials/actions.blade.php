<div class="flex flex-wrap items-center justify-end gap-2">
    <a href="{{ route('master.pengguna.edit', $user) }}" class="text-xs font-semibold" style="color:#4a9b7a;">Ubah</a>
    <form method="POST" action="{{ route('master.pengguna.reset-password', $user) }}" class="inline js-reset-password">
        @csrf
        <button type="submit" class="text-xs font-semibold" style="color:#1a4a6b;">Reset sandi</button>
    </form>
    <form method="POST" action="{{ route('master.pengguna.destroy', $user) }}" class="inline form-hapus-user">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-xs font-semibold" style="color:#c0392b;">Hapus</button>
    </form>
</div>
