<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Cie10Seeder extends Seeder
{
    public function run(): void
    {
        $codigos = [
            // K00: Trastornos del desarrollo y de la erupción de los dientes
            ['codigo' => 'K00.0', 'nombre' => 'Anodoncia (hipodoncia / oligodoncia)', 'categoria' => 'Desarrollo dental'],
            ['codigo' => 'K00.1', 'nombre' => 'Dientes supernumerarios (mesiodens, distomolar)', 'categoria' => 'Desarrollo dental'],
            ['codigo' => 'K00.2', 'nombre' => 'Anomalías del tamaño y de la forma del diente', 'categoria' => 'Desarrollo dental'],
            ['codigo' => 'K00.3', 'nombre' => 'Dientes moteados (Fluorosis dental)', 'categoria' => 'Desarrollo dental'],
            ['codigo' => 'K00.4', 'nombre' => 'Alteraciones en la formación dentaria (hipoplasia del esmalte)', 'categoria' => 'Desarrollo dental'],
            ['codigo' => 'K00.6', 'nombre' => 'Alteraciones en la erupción dentaria (persistencia de diente temporal)', 'categoria' => 'Desarrollo dental'],

            // K01: Dientes incluidos e impactados
            ['codigo' => 'K01.0', 'nombre' => 'Dientes incluidos', 'categoria' => 'Erupción e inclusión'],
            ['codigo' => 'K01.1', 'nombre' => 'Dientes impactados (terceros molares / cordales)', 'categoria' => 'Erupción e inclusión'],

            // K02: Caries dental
            ['codigo' => 'K02.0', 'nombre' => 'Caries limitada al esmalte (mancha blanca)', 'categoria' => 'Caries dental'],
            ['codigo' => 'K02.1', 'nombre' => 'Caries de la dentina', 'categoria' => 'Caries dental'],
            ['codigo' => 'K02.2', 'nombre' => 'Caries del cemento (radicular)', 'categoria' => 'Caries dental'],
            ['codigo' => 'K02.3', 'nombre' => 'Caries dentaria detenida', 'categoria' => 'Caries dental'],
            ['codigo' => 'K02.8', 'nombre' => 'Otras caries dentales especificadas', 'categoria' => 'Caries dental'],
            ['codigo' => 'K02.9', 'nombre' => 'Caries dental, no especificada', 'categoria' => 'Caries dental'],

            // K03: Otras enfermedades de los tejidos duros de los dientes
            ['codigo' => 'K03.0', 'nombre' => 'Atrición excesiva de los dientes (bruxismo / desgaste oclusal)', 'categoria' => 'Tejidos duros'],
            ['codigo' => 'K03.1', 'nombre' => 'Abrasión de los dientes (por cepillado o hábitos)', 'categoria' => 'Tejidos duros'],
            ['codigo' => 'K03.2', 'nombre' => 'Erosión de los dientes', 'categoria' => 'Tejidos duros'],
            ['codigo' => 'K03.6', 'nombre' => 'Depósitos [acreciones] en los dientes (cálculo dental / placa / sarro)', 'categoria' => 'Tejidos duros'],
            ['codigo' => 'K03.8', 'nombre' => 'Otras enfermedades especificadas de los tejidos duros (hipersensibilidad dentinaria)', 'categoria' => 'Tejidos duros'],

            // K04: Enfermedades de la pulpa y de los tejidos periapicales
            ['codigo' => 'K04.0', 'nombre' => 'Pulpitis (reversible / irreversible aguda o crónica)', 'categoria' => 'Endodoncia y pulpa'],
            ['codigo' => 'K04.1', 'nombre' => 'Necrosis de la pulpa (gangrena pulpar)', 'categoria' => 'Endodoncia y pulpa'],
            ['codigo' => 'K04.2', 'nombre' => 'Degeneración de la pulpa (calcificaciones / dentículos)', 'categoria' => 'Endodoncia y pulpa'],
            ['codigo' => 'K04.4', 'nombre' => 'Periodontitis apical aguda originada en la pulpa', 'categoria' => 'Endodoncia y pulpa'],
            ['codigo' => 'K04.5', 'nombre' => 'Periodontitis apical crónica (granuloma apical)', 'categoria' => 'Endodoncia y pulpa'],
            ['codigo' => 'K04.6', 'nombre' => 'Absceso periapical con fístula', 'categoria' => 'Endodoncia y pulpa'],
            ['codigo' => 'K04.7', 'nombre' => 'Absceso periapical sin fístula', 'categoria' => 'Endodoncia y pulpa'],
            ['codigo' => 'K04.8', 'nombre' => 'Quiste radicular (apical / periapical)', 'categoria' => 'Endodoncia y pulpa'],

            // K05: Gingivitis y enfermedades periodontales
            ['codigo' => 'K05.0', 'nombre' => 'Gingivitis aguda (asociada a placa / ulceronecrotizante)', 'categoria' => 'Periodoncia'],
            ['codigo' => 'K05.1', 'nombre' => 'Gingivitis crónica', 'categoria' => 'Periodoncia'],
            ['codigo' => 'K05.2', 'nombre' => 'Periodontitis aguda (absceso periodontal / pericoronaritis)', 'categoria' => 'Periodoncia'],
            ['codigo' => 'K05.3', 'nombre' => 'Periodontitis crónica (leve, moderada o severa)', 'categoria' => 'Periodoncia'],
            ['codigo' => 'K05.4', 'nombre' => 'Periodontosis (periodontitis agresiva)', 'categoria' => 'Periodoncia'],

            // K06: Otros trastornos de la encía y de la zona edéntula
            ['codigo' => 'K06.0', 'nombre' => 'Retracción gingival (recesión gingival)', 'categoria' => 'Periodoncia'],
            ['codigo' => 'K06.1', 'nombre' => 'Hiperplasia gingival', 'categoria' => 'Periodoncia'],

            // K07: Anomalías dentofaciales (incluso la maloclusión)
            ['codigo' => 'K07.2', 'nombre' => 'Anomalías de la relación entre los arcos dentarios (Maloclusión Clase I, II, III)', 'categoria' => 'Ortodoncia y ATM'],
            ['codigo' => 'K07.3', 'nombre' => 'Anomalías de la posición del diente (apiñamiento, diastema, rotación)', 'categoria' => 'Ortodoncia y ATM'],
            ['codigo' => 'K07.6', 'nombre' => 'Trastornos de la articulación temporomandibular (ATM)', 'categoria' => 'Ortodoncia y ATM'],

            // K08: Otros trastornos de los dientes y de sus estructuras de sostén
            ['codigo' => 'K08.1', 'nombre' => 'Pérdida de dientes debida a accidente, extracción o enfermedad periodontal local (Edentulismo)', 'categoria' => 'Prótesis y cirugía'],
            ['codigo' => 'K08.3', 'nombre' => 'Raíz dental retenida (resto radicular)', 'categoria' => 'Prótesis y cirugía'],

            // K10 - K14: Maxilares, glándulas salivales y mucosa oral
            ['codigo' => 'K10.3', 'nombre' => 'Alveolitis del maxilar (alveolitis seca post-extracción)', 'categoria' => 'Cirugía y patología oral'],
            ['codigo' => 'K12.0', 'nombre' => 'Estomatitis aftosa recurrente (aftas orales)', 'categoria' => 'Patología oral'],
            ['codigo' => 'K12.1', 'nombre' => 'Otras formas de estomatitis (estomatitis subprotésica)', 'categoria' => 'Patología oral'],
            ['codigo' => 'K13.0', 'nombre' => 'Enfermedades de los labios (queilitis angular)', 'categoria' => 'Patología oral'],
            ['codigo' => 'S02.5', 'nombre' => 'Fractura de los dientes (traumatismo dental)', 'categoria' => 'Traumatología dental'],
            ['codigo' => 'Z01.2', 'nombre' => 'Examen odontológico (control preventivo / niño o adulto sano)', 'categoria' => 'Prevención y control'],
        ];

        $now = now();
        foreach ($codigos as &$c) {
            $c['activo'] = true;
            $c['created_at'] = $now;
            $c['updated_at'] = $now;
        }

        DB::table('cie10')->upsert($codigos, ['codigo'], ['nombre', 'categoria', 'activo', 'updated_at']);
    }
}

