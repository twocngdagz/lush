<players-form :errors="{{ $errors }}"
             :old_input="{{ json_encode(session()->getOldInput()) }}">
</players-form>


@push('before-scripts')
    <script>
        window.players_shared_state = {
            players_data: {!! (isset($players)) ? $players->toJson() : '[{}]' !!},
            single: {!! json_encode($single ?? false) !!},
            no_edit: {!! json_encode($no_edit ?? false) !!},
            can_add_multiple: {!! json_encode($can_add_multiple ?? false) !!},
            error_list: {!! $errors !!},
            id_types: {!! json_encode($idTypes ?? false) !!},
            genders: {!! json_encode($playerGender ?? false) !!},
            ranks: {!! json_encode($lushRanks ?? false) !!},
            countries: {!! json_encode($countries ?? false) !!},
            hasPlayerError: function(name, index) {
                return this.error_list && this.error_list[name] && this.error_list[name][index] !== undefined;
            }
        };
    </script>
@endpush
