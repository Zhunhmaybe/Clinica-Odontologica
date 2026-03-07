<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION validar_cedula_ec(cedula_text TEXT)
            RETURNS BOOLEAN AS $$
            DECLARE
                total INT := 0;
                longitud INT;
                digito_region INT;
                tercer_digito INT;
                ultimo_digito INT;
                i INT;
                valor INT;
                coeficiente INT;
                residuo INT;
                resultado INT;
            BEGIN
                IF cedula_text IS NULL OR cedula_text !~ '^[0-9]{10}$' THEN
                    RETURN FALSE;
                END IF;

                digito_region := substring(cedula_text from 1 for 2)::INT;
                IF (digito_region < 1 OR digito_region > 24) AND digito_region <> 30 THEN
                    RETURN FALSE;
                END IF;
                tercer_digito := substring(cedula_text from 3 for 1)::INT;
                IF tercer_digito >= 6 THEN
                    RETURN FALSE;
                END IF;

                FOR i IN 1..9 LOOP
                    valor := substring(cedula_text from i for 1)::INT;
                    IF (i % 2) <> 0 THEN
                        valor := valor * 2;
                    ELSE
                        valor := valor * 1;
                    END IF;

                    IF valor >= 10 THEN
                        valor := valor - 9;
                    END IF;

                    total := total + valor;
                END LOOP;

                ultimo_digito := substring(cedula_text from 10 for 1)::INT;
                residuo := total % 10;
                
                IF residuo = 0 THEN
                    resultado := 0;
                ELSE
                    resultado := 10 - residuo;
                END IF;

                IF resultado = ultimo_digito THEN
                    RETURN TRUE;
                ELSE
                    RETURN FALSE;
                END IF;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // Esto evita que se inserten cédulas falsas incluso si fallas en PHP
        DB::unprepared("
            CREATE OR REPLACE FUNCTION trigger_validar_cedula_guardado()
            RETURNS TRIGGER AS $$
            BEGIN
                IF NOT validar_cedula_ec(NEW.cedula) THEN
                    RAISE EXCEPTION 'PL/pgSQL: La cédula ecuatoriana ingresada (%) no es válida.', NEW.cedula;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS trg_check_cedula_pacientes ON pacientes;
            CREATE TRIGGER trg_check_cedula_pacientes
            BEFORE INSERT OR UPDATE ON pacientes
            FOR EACH ROW EXECUTE FUNCTION trigger_validar_cedula_guardado();
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_check_cedula_pacientes ON pacientes");
        DB::unprepared("DROP FUNCTION IF EXISTS trigger_validar_cedula_guardado");
        DB::unprepared("DROP FUNCTION IF EXISTS validar_cedula_ec");
    }
};