@extends('layouts.p1_home')
@section('title','Inicio')

@section('content')
    <section class="hero text-center">
        <div class="container">
            <h1>Tu sonrisa, nuestra prioridad</h1>
            <p class="mt-3">
                En el Consultorio Odontológico Danny brindamos atención profesional,
                humana y de calidad para el cuidado integral de tu salud dental.
            </p>
            {{-- <a href="{{ route('login') }}" class="btn btn-light mt-4">Agendar Cita</a> --}}
        </div>
    </section>

    <section class="py-5">
        <div class="container text-center">
            <h2 class="section-title mb-4">Nuestros Servicios</h2>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="service-icon">🦷</div>
                    <h5 class="mt-3">Odontología General</h5>
                    <p>Diagnóstico, limpieza dental y tratamientos preventivos.</p>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="service-icon">😁</div>
                    <h5 class="mt-3">Estética Dental</h5>
                    <p>Blanqueamiento, carillas y diseño de sonrisa.</p>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="service-icon">🛡️</div>
                    <h5 class="mt-3">Ortodoncia</h5>
                    <p>Brackets y tratamientos para una correcta alineación dental.</p>
                </div>
            </div>
        </div>
    </section>


    <section class="py-5 bg-soft-blue">
        <div class="container">
            <div class="row align-items-center">


                <div class="col-md-6 mb-4 mb-md-0">
                    <h2 class="section-title">¿Quiénes Somos?</h2>

                    <p class="mt-3">
                        En el <strong>Consultorio Odontológico Danny</strong> nos
                        especializamos en brindar atención dental integral,
                        combinando experiencia profesional, tecnología moderna
                        y un trato humano y cercano.
                    </p>

                    <p>
                        Nuestro compromiso es cuidar tu sonrisa mediante
                        tratamientos seguros, personalizados y orientados al
                        bienestar y la confianza de cada paciente.
                    </p>

                    <ul class="mt-3">
                        <li>✔ Atención personalizada</li>
                        <li>✔ Tecnología odontológica moderna</li>
                        <li>✔ Profesionales calificados</li>
                        <li>✔ Ambiente seguro y confortable</li>
                    </ul>
                </div>


                <div class="col-md-6 text-center">
                    <div class="about-logo-container">
                        <img src="/images/logo-danny.png"
                            alt="Consultorio Danny"
                            class="img-fluid about-logo-img">
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
