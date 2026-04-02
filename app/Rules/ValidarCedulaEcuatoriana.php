<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ValidarCedulaEcuatoriana implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
    }
    public function passes($attribute, $value)
    {
        $resultado = DB::select('SELECT validar_cedula_ec(?) as es_valida', [$value]);
        return $resultado[0]->es_valida;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'La cédula ingresada no es válida o no existe.';
    }
}
