@extends('sistema.layouts.app')

@section('content')
    <div class="container mt-4">
        <form action="{{ route('schedule.update') }}" method="POST">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    @php
                        $days = ['domingo', 'segunda', 'terça', 'quarta', 'quinta', 'sexta', 'sábado'];
                    @endphp

                    @foreach ($days as $index => $day)
                        @php
                            $slot = $availability->firstWhere('day_of_week', $day);
                        @endphp

                        <div class="col">
                            <div class="card">
                                <div class="card-header" id="{{ 'header-' . $index }}"
                                    style="background-color: {{ $slot ? '#e2ffee' : '#ffe5e2' }}">{{ ucfirst($day) }}</div>
                                <div class="card-body">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="{{ 'switch-' . $index }}"
                                            name="days[{{ $day }}][active]"
                                            onchange="toggleStatus(this, '{{ 'header-' . $index }}')"
                                            {{ $slot ? 'checked' : '' }}>
                                        <label class="custom-control-label"
                                            for="{{ 'switch-' . $index }}">{{ $slot ? 'Ativo' : 'Inativo' }}</label>
                                    </div>
                                    <div class="form-group">
                                        <label>Hora de início:</label>
                                        <input type="time" class="form-control"
                                            name="days[{{ $day }}][start_time]"
                                            value="{{ $slot->start_time ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Hora de término:</label>
                                        <input type="time" class="form-control"
                                            name="days[{{ $day }}][end_time]"
                                            value="{{ $slot->end_time ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </form>
    </div>

    <script>
        function toggleStatus(element, headerId) {
            var header = document.getElementById(headerId);
            if (element.checked) {
                element.nextElementSibling.textContent = 'Ativo';
                header.style.backgroundColor = '#e2ffee';
            } else {
                element.nextElementSibling.textContent = 'Inativo';
                header.style.backgroundColor = '#ffe5e2';
            }
        }
    </script>
@endsection
