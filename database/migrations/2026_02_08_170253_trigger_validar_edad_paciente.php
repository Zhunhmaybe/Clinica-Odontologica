<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION check_validaciones_paciente()
            RETURNS TRIGGER AS $$
            BEGIN
                IF NEW.fecha_nacimiento > CURRENT_DATE THEN
                     RAISE EXCEPTION 'PL/pgSQL: La fecha de nacimiento no puede ser futura.';
                END IF;
                IF AGE(CURRENT_DATE, NEW.fecha_nacimiento) < INTERVAL '1 year' THEN
                    RAISE EXCEPTION 'PL/pgSQL: El paciente no cumple la edad mínima de 1 año.';
                END IF;
                
                IF NEW.email IS NOT NULL AND TRIM(NEW.email) <> '' THEN
                    
                    IF (TG_OP = 'INSERT') THEN
                        IF EXISTS (SELECT 1 FROM pacientes WHERE email = NEW.email) THEN
                            RAISE EXCEPTION 'PL/pgSQL: El correo electrónico % ya está registrado.', NEW.email;
                        END IF;
                    
                    ELSIF (TG_OP = 'UPDATE') THEN
                        IF (NEW.email <> OLD.email) THEN
                            IF EXISTS (SELECT 1 FROM pacientes WHERE email = NEW.email AND id <> OLD.id) THEN
                                RAISE EXCEPTION 'PL/pgSQL: El correo electrónico % ya está registrado por otro paciente.', NEW.email;
                            END IF;
                        END IF;
                    END IF;
                    
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_validaciones_pacientes ON pacientes;
            
            CREATE TRIGGER trg_validaciones_pacientes
            BEFORE INSERT OR UPDATE ON pacientes
            FOR EACH ROW EXECUTE FUNCTION check_validaciones_paciente();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Borramos el trigger primero
        DB::unprepared("DROP TRIGGER IF EXISTS trg_validaciones_pacientes ON pacientes");
        
        // Borramos la función después
        DB::unprepared("DROP FUNCTION IF EXISTS check_validaciones_paciente");
    }
};