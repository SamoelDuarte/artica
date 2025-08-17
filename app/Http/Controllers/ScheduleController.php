<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AvailableSlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $availableSlots = AvailableSlot::all();
        return view('sistema.schedule.index')->with('availability', $availableSlots);
    }
    public function update(Request $request)
    {
        foreach ($request->days as $day => $data) {
            // Ajuste aqui para verificar a existência das horas
            if (!empty($data['start_time']) && !empty($data['end_time'])) {
                $slot = AvailableSlot::where('day_of_week', $day)->first();
    
                if ($slot) {
                    // Atualiza apenas se o checkbox 'active' estiver marcado
                    if (isset($data['active'])) {
                        $slot->update([
                            'start_time' => $data['start_time'],
                            'end_time' => $data['end_time'],
                        ]);
                    } else {
                        $slot->delete();
                    }
                } else {
                    // Cria um novo registro apenas se o checkbox 'active' estiver marcado
                    if (isset($data['active'])) {
                        AvailableSlot::create([
                            'day_of_week' => $day,
                            'start_time' => $data['start_time'],
                            'end_time' => $data['end_time'],
                        ]);
                    }
                }
            }
        }
    
        return redirect()->back();
    }
    

    private function insertAvailableSlots()
    {
        $daysOfWeek = ['segunda', 'terça', 'quarta', 'quinta', 'sexta'];

        foreach ($daysOfWeek as $day) {
            // Adicione os horários disponíveis para cada dia da semana
            // Aqui, estamos adicionando dois intervalos de tempo para cada dia (manhã e tarde)
            // Você pode adicionar mais intervalos de tempo conforme necessário
            DB::table('available_slots')->insert([
                [
                    'day_of_week' => $day,
                    'start_time' => '08:00',
                    'end_time' => '13:00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }
    }
}
