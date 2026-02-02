@extends('layouts.landing')

@section('title', 'Privacy Policy - IADC Suez University Student Chapter')
@section('meta_description', 'Privacy Policy for IADC Suez University Student Chapter. Learn how we handle your data on our non-profit educational platform.')

@section('header')
<div class="header-main-image text-white relative">
    <div class="header-overlay"></div>
    <div class="container relative z-10">
        <div class="row">
            <div class="col-md-12">
                <h1 class="display-4 font-weight-bold mb-4">Privacy Policy</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="{{ route('index') }}" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Privacy Policy</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<style>
    .header-main-image {
        background: linear-gradient(rgba(17, 24, 39, 0.8), rgba(17, 24, 39, 0.8)), url('{{ asset('assets/images/brand/background.jpg') }}');
        background-size: cover;
        background-position: center;
        padding: 6rem 0;
        margin-bottom: 3rem;
    }
    .privacy-content h2 {
        color: #ab1f2e;
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-weight: 600;
    }
    .privacy-content h3 {
        font-size: 1.25rem;
        font-weight: 600;
        margin-top: 1.5rem;
        color: #333;
    }
    .privacy-content p {
        color: #555;
        line-height: 1.7;
        margin-bottom: 1rem;
    }
    .privacy-content ul {
        color: #555;
        margin-bottom: 1.5rem;
    }
    .privacy-content li {
        margin-bottom: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body p-5 privacy-content">
                    <div class="mb-4">
                        <p class="lead">Last updated: {{ date('F d, Y') }}</p>
                        <p>At <strong>IADC Suez University Student Chapter</strong>, accessible from {{ config('app.url') }}, one of our main priorities is the privacy of our visitors. This Privacy Policy document contains types of information that is collected and recorded by IADC Suez University Student Chapter and how we use it.</p>
                        <p>If you have additional questions or require more information about our Privacy Policy, do not hesitate to contact us.</p>
                    </div>

                    <h2>General Information</h2>
                    <p>This "Privacy Policy" applies to legitimate users of the site, whether registered members or visitors. We are a non-profit educational student chapter affiliated with the <strong>Faculty of Petroleum and Mining Engineering at Suez University</strong> and the <strong>International Association of Drilling Contractors (IADC)</strong>. Our platform is dedicated to helping students improve their skills and bridging the gap between academia and the drilling industry.</p>

                    <h2>Information We Collect</h2>
                    <p>The personal information that you are asked to provide, and the reasons why you are asked to provide it, will be made clear to you at the point we ask you to provide your personal information.</p>
                    
                    <h3>Registered Members</h3>
                    <p>When you register for an account (Highboard or Board member), we may ask for your contact information, including items such as:</p>
                    <ul>
                        <li>Name</li>
                        <li>University Email Address</li>
                        <li>Phone Number</li>
                        <li>University ID / Academic Information</li>
                        <li>Profile Picture</li>
                    </ul>

                    <h3>Google Calendar Integration</h3>
                    <p>Our platform integrates with Google Calendar to help board members manage committee sessions. If you choose to connect your Google account:</p>
                    <ul>
                        <li>We request access to manage your calendar events solely for the purpose of scheduling chapter meetings.</li>
                        <li>We store your Google tokens securely and do not share this access with third parties.</li>
                        <li>You can revoke this access at any time via your Google Account security settings.</li>
                    </ul>

                    <h2>How We Use Your Information</h2>
                    <p>We use the information we collect in various ways, including to:</p>
                    <ul>
                        <li>Provide, operate, and maintain our educational platform</li>
                        <li>Improve, personalize, and expand our services for students</li>
                        <li>Understand and analyze how you use our website</li>
                        <li>Develop new products, services, features, and functionality</li>
                        <li>Communicate with you regarding chapter updates, events, and educational materials</li>
                        <li>Send you emails relating to your committee tasks or general chapter news (Newsletter)</li>
                        <li>Prevent fraud and ensure system security</li>
                    </ul>

                    <h2>Log Files</h2>
                    <p>IADC Suez University Student Chapter follows a standard procedure of using log files. These files log visitors when they visit websites. All hosting companies do this and a part of hosting services' analytics. The information collected by log files include internet protocol (IP) addresses, browser type, Internet Service Provider (ISP), date and time stamp, referring/exit pages, and possibly the number of clicks. These are not linked to any information that is personally identifiable. The purpose of the information is for analyzing trends, administering the site, tracking users' movement on the website, and gathering demographic information.</p>

                    <h2>Non-Profit & Educational Purpose</h2>
                    <p>We operate as a non-profit student organization. We do not sell, trade, or otherwise transfer your personally identifiable information to outside parties for commercial purposes. Data may be shared with the university administration or the main IADC organization only when necessary for official chapter reporting or accreditation.</p>

                    <h2>Third Party Privacy Policies</h2>
                    <p>Our Privacy Policy does not apply to other advertisers or websites. Thus, we are advising you to consult the respective Privacy Policies of these third-party ad servers for more detailed information. It may include their practices and instructions about how to opt-out of certain options.</p>

                    <h2>Children's Information</h2>
                    <p>Another part of our priority is adding protection for children while using the internet. We encourage parents and guardians to observe, participate in, and/or monitor and guide their online activity.</p>
                    <p>IADC Suez University Student Chapter does not knowingly collect any Personal Identifiable Information from children under the age of 13. If you think that your child provided this kind of information on our website, we strongly encourage you to contact us immediately and we will do our best efforts to promptly remove such information from our records.</p>

                    <h2>Contact Us</h2>
                    <p>If you have any questions about our Privacy Policy, please do not hesitate to contact us:</p>
                    <ul>
                        <li>Email: contact@iadcsuez.org</li>
                        <li>By visiting the contact section on our website</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
