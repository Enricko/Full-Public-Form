@extends('index')

@section('title', 'About - PublicForum')

@section('content')

<div class="container">

  <div class="about-header bg-danger text-white text-center py-5 mb-5 rounded">
    <h1 class="display-4 mb-2">About PublicForum</h1>
    <p class="lead mx-auto" style="max-width: 700px;">
      PublicForum is home to thousands of communities, endless conversation, and
      authentic human connection. Whether you're into breaking news, sports, TV fan
      theories, or a never-ending stream of the internet's cutest animals, there's a
      community on PublicForum for you.
    </p>
  </div>

  <div class="section mb-5">
    <h2 class="text-center mb-4">Our Mission</h2>
    <div class="text-center mb-4">
      <div class="mx-auto" style="width: 50px; height: 3px; background-color: #dc3545;"></div>
    </div>

    <div class="card bg-light">
      <div class="card-body p-4 text-center">
        <p class="mb-0">
          We aim to provide a respectful, diverse, and enriching digital environment where meaningful conversations and knowledge exchanges thrive.
          PublicForum is built to be a space where everyone can contribute, learn, and grow together through discussions that matter.
        </p>
      </div>
    </div>
  </div>

  <div class="section mb-5">
    <h2 class="text-center mb-4">Core Values</h2>
    <div class="text-center mb-4">
      <div class="mx-auto" style="width: 50px; height: 3px; background-color: #dc3545;"></div>
    </div>

    <div class="row g-4">

      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body p-4">
            <h3 class="card-title text-danger mb-3">Inclusivity</h3>
            <p class="card-text">
              We are committed to creating an environment where everyone feels welcome, respected, and empowered. We actively seek to include
              individuals from diverse backgrounds, cultures, identities, and experiences. By embracing a wide range of perspectives, we foster a
              stronger, more innovative, and empathetic community. Every voice matters, and we work diligently to ensure that all users feel seen,
              heard, and valued.
            </p>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body p-4">
            <h3 class="card-title text-danger mb-3">Integrity</h3>
            <p class="card-text">
              Our foundation is built on trust, which we uphold through transparency, honesty, and accountability in all that we do. We believe in
              doing the right thing, even when it's difficult or inconvenient. This means being truthful in our communications, respectful in our
              interactions, and responsible stewards of the platforms and communities we serve. Integrity guides our decisions and actions as we
              strive to earn and maintain the trust of those who rely on us.
            </p>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body p-4">
            <h3 class="card-title text-danger mb-3">Curiosity</h3>
            <p class="card-text">
              We believe that progress begins with curiosity—a desire to understand more deeply, to ask meaningful questions, and to challenge the
              status quo. Our community is one where learning is celebrated, and exploration is encouraged. We support a culture that values
              open-minded inquiry, continuous growth, and the pursuit of knowledge for the betterment of individuals and the collective.
            </p>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body p-4">
            <h3 class="card-title text-danger mb-3">Respectful Discourse</h3>
            <p class="card-text">
              We are dedicated to fostering a community where open and respectful conversations can flourish. Even when opinions differ, we believe in
              engaging with one another thoughtfully and kindly. We encourage active listening, empathy, and a willingness to understand opposing
              views. Everyone deserves to express themselves without fear of hostility or judgment, and we work to ensure that dialogue remains
              constructive, inclusive, and civil.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="section mb-5">
    <h2 class="text-center mb-4">Meet the Team</h2>
    <div class="text-center mb-4">
      <div class="mx-auto" style="width: 50px; height: 3px; background-color: #dc3545;"></div>
    </div>

    <div class="row row-cols-1 row-cols-md-4 g-4">

      <div class="col">
        <div class="card h-100 text-center">
          <div class="pt-4">
            <img src="../assets/images/team/profile.png" class="rounded-circle border border-3 border-danger" width="100" height="100"
              alt="Bombardino">
          </div>
          <div class="card-body">
            <h5 class="card-title">Bombardino</h5>
            <p class="card-subtitle text-muted mb-2">Founder & CEO</p>
            <p class="card-text small text-muted">
              Guiding the vision of a better discourse-driven internet with fiery passion.
            </p>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="card h-100 text-center">
          <div class="pt-4">
            <img src="../assets/images/team/profile.png" class="rounded-circle border border-3 border-danger" width="100" height="100"
              alt="Crocodilo">
          </div>
          <div class="card-body">
            <h5 class="card-title">Crocodilo</h5>
            <p class="card-subtitle text-muted mb-2">CTO</p>
            <p class="card-text small text-muted">
              Engineering the foundations of a more thoughtful online community.
            </p>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="card h-100 text-center">
          <div class="pt-4">
            <img src="../assets/images/team/profile.png" class="rounded-circle border border-3 border-danger" width="100" height="100"
              alt="Sinful John">
          </div>
          <div class="card-body">
            <h5 class="card-title">Sinful John</h5>
            <p class="card-subtitle text-muted mb-2">Community Director</p>
            <p class="card-text small text-muted">
              Building bridges between people while upholding community trust.
            </p>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="card h-100 text-center">
          <div class="pt-4">
            <img src="../assets/images/team/profile.png" class="rounded-circle border border-3 border-danger" width="100" height="100" alt="Steve">
          </div>
          <div class="card-body">
            <h5 class="card-title">Steve</h5>
            <p class="card-subtitle text-muted mb-2">Head of Content</p>
            <p class="card-text small text-muted">
              Curating and surfacing valuable knowledge shared across the platform.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="text-center text-muted mb-4">
    <p class="mb-1">&copy; 2025 PublicForum. All rights reserved.</p>
    <p class="small">Shaping the future of online discourse, one conversation at a time.</p>
  </div>
</div>

@endsection