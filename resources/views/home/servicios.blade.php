@extends('layouts.p1_home')
@section('tittle','Servicios')
@push('styles')
    @vite('resources/css/navbar/servicios.css')
@endpush

@section('content')
    <section class="py-5 bg-white-custom">
        <div class="container">

            <div class="text-center mb-5">
                <h2 class="section-title">Nuestros Servicios</h2>
                <p class="mt-2 text-muted">
                    Brindamos soluciones odontológicas integrales con calidad,
                    seguridad y atención personalizada.
                </p>
            </div>

            <div class="row g-4">


                <div class="col-md-4">
                    <div class="card service-card text-center p-4">
                        <div class="service-icon">🦷</div>
                        <h5>Odontología General</h5>
                        <p>
                            Diagnóstico, limpieza dental, restauraciones y
                            tratamientos preventivos para el cuidado de tu salud bucal.
                        </p>
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="card service-card text-center p-4">
                        <div class="service-icon">😁</div>
                        <h5>Estética Dental</h5>
                        <p>
                            Blanqueamiento dental, carillas estéticas y diseño
                            de sonrisa para mejorar tu imagen y confianza.
                        </p>
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="card service-card text-center p-4">
                        <div class="service-icon">🛡️</div>
                        <h5>Ortodoncia</h5>
                        <p>
                            Tratamientos con brackets y alineadores para corregir
                            la posición dental y mejorar la mordida.
                        </p>
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="card service-card text-center p-4">
                        <div class="service-icon">🪥</div>
                        <h5>Profilaxis Dental</h5>
                        <p>
                            Limpieza profunda para prevenir caries, gingivitis
                            y mantener una sonrisa saludable.
                        </p>
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="card service-card text-center p-4">
                        <div class="service-icon">🦠</div>
                        <h5>Endodoncia</h5>
                        <p>
                            Tratamiento de conductos para eliminar infecciones
                            y conservar las piezas dentales.
                        </p>
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="card service-card text-center p-4">
                        <div class="service-icon">🦷</div>
                        <h5>Prótesis Dental</h5>
                        <p>
                            Rehabilitación oral mediante prótesis fijas o removibles
                            para recuperar funcionalidad y estética.
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </section>
@endsection
