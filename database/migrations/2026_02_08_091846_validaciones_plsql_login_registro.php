<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION validar_fuerza_password(password_texto TEXT)
            RETURNS BOOLEAN AS $$
            BEGIN
                IF length(password_texto) < 10 THEN
                    RAISE EXCEPTION 'PL/pgSQL: La contraseña debe tener al menos 10 caracteres.';
                END IF;

                IF password_texto !~ '[A-Z]' THEN
                    RAISE EXCEPTION 'PL/pgSQL: La contraseña debe contener al menos una letra mayúscula.';
                END IF;
                IF password_texto !~ '[a-z]' THEN
                    RAISE EXCEPTION 'PL/pgSQL: La contraseña debe contener al menos una letra minúscula.';
                END IF;

                IF password_texto !~ '[0-9]' THEN
                    RAISE EXCEPTION 'PL/pgSQL: La contraseña debe contener al menos un número.';
                END IF;
                IF password_texto !~ '[^a-zA-Z0-9]' THEN
                    RAISE EXCEPTION 'PL/pgSQL: La contraseña debe contener al menos un carácter especial (Ej: @, #, $, &).';
                END IF;

                RETURN TRUE;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION validar_formato_email_func()
            RETURNS TRIGGER AS $$
            BEGIN
                IF NEW.email !~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
                    RAISE EXCEPTION 'PL/pgSQL: El correo electrónico (%) no tiene un formato válido.', NEW.email;
                END IF;
                
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_validar_email_usuarios ON usuarios;
            CREATE TRIGGER trg_validar_email_usuarios
            BEFORE INSERT OR UPDATE ON usuarios
            FOR EACH ROW EXECUTE FUNCTION validar_formato_email_func();
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_validar_email_usuarios ON usuarios");
        DB::unprepared("DROP FUNCTION IF EXISTS validar_formato_email_func");
        DB::unprepared("DROP FUNCTION IF EXISTS validar_fuerza_password");
    }
};