<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidarCedulaEcuatoriana implements ValidationRule
{
    /**
     * Valida si un string corresponde a una cédula ecuatoriana válida
     * mediante el algoritmo oficial de Módulo 10.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) && !is_numeric($value)) {
            $fail('La cédula debe ser numérica.');
            return;
        }

        $cedula = trim((string) $value);

        // 1. Debe tener exactamente 10 dígitos numéricos
        if (!preg_match('/^[0-9]{10}$/', $cedula)) {
            $fail('La cédula ecuatoriana debe tener exactamente 10 dígitos numéricos.');
            return;
        }

        // 2. Los dos primeros dígitos corresponden a las provincias (01 a 24) o 30 (consulados)
        $provincia = (int) substr($cedula, 0, 2);
        if (($provincia < 1 || $provincia > 24) && $provincia !== 30) {
            $fail('El código de provincia de la cédula no es válido.');
            return;
        }

        // 3. El tercer dígito para personas naturales debe ser menor a 6 (0 al 5)
        $tercerDigito = (int) $cedula[2];
        if ($tercerDigito >= 6) {
            $fail('El tercer dígito de la cédula debe ser menor a 6.');
            return;
        }

        // 4. Algoritmo Módulo 10 ecuatoriano
        $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $suma = 0;

        for ($i = 0; $i < 9; $i++) {
            $producto = (int) $cedula[$i] * $coeficientes[$i];
            $suma += ($producto >= 10) ? ($producto - 9) : $producto;
        }

        $residuo = $suma % 10;
        $digitoVerificadorCalculado = ($residuo === 0) ? 0 : (10 - $residuo);
        $digitoVerificadorReal = (int) $cedula[9];

        if ($digitoVerificadorCalculado !== $digitoVerificadorReal) {
            $fail('El número de cédula ingresado no es válido según el registro oficial de identidad.');
        }
    }

    /**
     * Compatibilidad con validadores antiguos de Laravel
     */
    public function passes($attribute, $value)
    {
        $esValida = true;
        $this->validate($attribute, $value, function() use (&$esValida) {
            $esValida = false;
        });
        return $esValida;
    }

    public function message()
    {
        return 'La cédula ingresada no es válida según el registro oficial de identidad.';
    }
}
