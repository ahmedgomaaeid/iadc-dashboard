<style>
    .team-section {
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        padding: 80px 0;
        overflow: hidden;
    }
    
    .team-swiper {
        padding: 30px 10px 60px;
        overflow: visible;
    }
    
    .team-card {
        background: #ffffff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
    }
    
    .team-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
    }
    
    .team-card-image {
        position: relative;
        overflow: hidden;
    }
    
    .team-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .team-card:hover .team-card-image img {
        transform: scale(1.08);
    }
    
    .team-card-image::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 120px;
        background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
        pointer-events: none;
    }
    
    .team-card-content {
        padding: 25px 20px;
        text-align: center;
        position: relative;
    }
    
    .team-card-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 5px;
        letter-spacing: -0.5px;
    }
    
    .team-card-role {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 15px;
        font-weight: 500;
    }
    
    .team-card-divider {
        width: 40px;
        height: 3px;
        background: linear-gradient(90deg, #ab1f2e, #d63447);
        margin: 0 auto 15px;
        border-radius: 2px;
    }
    
    .linkedin-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 24px;
        background: linear-gradient(135deg, #0077b5, #005885);
        color: #fff;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 119, 181, 0.3);
    }
    
    .linkedin-btn:hover {
        background: linear-gradient(135deg, #005885, #004165);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 119, 181, 0.4);
        color: #fff;
    }
    
    .linkedin-btn i {
        font-size: 1rem;
    }
    
    /* Decorative elements */
    .team-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #ab1f2e, #d63447, #ab1f2e);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .team-card:hover::before {
        opacity: 1;
    }
    
    /* Section styling */
    .team-section .section-subtitle {
        color: #ab1f2e;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 3px;
        font-size: 0.85rem;
        margin-bottom: 10px;
    }
    
    .team-section .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 15px;
    }
    
    /* Swiper Container */
    .team-swiper-container {
        position: relative;
        padding: 0;
    }
    
    /* Swiper Pagination */
    .team-swiper .swiper-pagination {
        bottom: 0;
    }
    
    .team-swiper .swiper-pagination-bullet {
        width: 10px;
        height: 10px;
        background: #ab1f2e;
        opacity: 0.4;
        transition: all 0.3s ease;
    }
    
    .team-swiper .swiper-pagination-bullet-active {
        opacity: 1;
        width: 28px;
        border-radius: 5px;
    }
    
    /* Active slide highlight */
    .swiper-slide-active .team-card {
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.12);
    }
    
    @media (max-width: 768px) {
        .team-section .section-title {
            font-size: 1.8rem;
        }
    }
</style>

<div class="team-section section" id="Team">
    <div class="container">
        <div class="text-center reveal">
            <p class="section-subtitle">Meet The Team</p>
            <h2 class="section-title">IADC Suez <span class="text-primary">Highboard</span></h2>
        </div>
        
        <div class="team-swiper-container">
            <div class="swiper team-swiper">
                <div class="swiper-wrapper">
                    <!-- Team Member 1 -->
                    <div class="swiper-slide">
                        <div class="team-card">
                            <div class="team-card-image">
                                <img src="{{ asset('assets/images/users/amr.webp') }}" alt="Amr Eldeeb">
                            </div>
                            <div class="team-card-content">
                                <div class="team-card-divider"></div>
                                <h4 class="team-card-name">Amr Eldeeb</h4>
                                <p class="team-card-role text-primary">Chairman</p>
                                <a href="https://www.linkedin.com/in/amr-eldeeb-pe" target="_blank" class="linkedin-btn">
                                    <i class="fa fa-linkedin"></i>
                                    Connect
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team Member 2 -->
                    <div class="swiper-slide">
                        <div class="team-card">
                            <div class="team-card-image">
                                <img src="{{ asset('assets/images/users/Ahmed Mobasher.webp') }}" alt="Ahmed Mobasher - Vice Chairman of IADC Suez University Student Chapter">
                            </div>
                            <div class="team-card-content">
                                <div class="team-card-divider"></div>
                                <h4 class="team-card-name">Ahmed Mobasher</h4>
                                <p class="team-card-role text-primary">Vice Chairman</p>
                                <a href="https://www.linkedin.com/in/ramseyy" target="_blank" class="linkedin-btn">
                                    <i class="fa fa-linkedin"></i>
                                    Connect
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team Member 3 -->
                    <div class="swiper-slide">
                        <div class="team-card">
                            <div class="team-card-image">
                                <img src="{{ asset('assets/images/users/hesham.webp') }}" alt="Eslam Hesham - PR and Treasury Manager of IADC Suez University Student Chapter">
                            </div>
                            <div class="team-card-content">
                                <div class="team-card-divider"></div>
                                <h4 class="team-card-name">Eslam Hesham</h4>
                                <p class="team-card-role text-primary">PR & Treasury Manager</p>
                                <a href="https://www.linkedin.com/in/esham-22b06a275" target="_blank" class="linkedin-btn">
                                    <i class="fa fa-linkedin"></i>
                                    Connect
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team Member 4 -->
                    <div class="swiper-slide">
                        <div class="team-card">
                            <div class="team-card-image">
                                <img src="{{ asset('assets/images/users/gomaa.webp') }}" alt="Ahmed Gomaa - IT Manager of IADC Suez University Student Chapter">
                            </div>
                            <div class="team-card-content">
                                <div class="team-card-divider"></div>
                                <h4 class="team-card-name">Ahmed Gomaa</h4>
                                <p class="team-card-role text-primary">IT Manager</p>
                                <a href="https://www.linkedin.com/in/ahmed-gomaa-eid" target="_blank" class="linkedin-btn">
                                    <i class="fa fa-linkedin"></i>
                                    Connect
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team Member 5 -->
                    <div class="swiper-slide">
                        <div class="team-card">
                            <div class="team-card-image">
                                <img src="{{ asset('assets/images/users/Saad.webp') }}" alt="Saad Waseem - Technical Manager of IADC Suez University Student Chapter">
                            </div>
                            <div class="team-card-content">
                                <div class="team-card-divider"></div>
                                <h4 class="team-card-name">Saad Waseem</h4>
                                <p class="team-card-role text-primary">Technical Manager</p>
                                <a href="https://www.linkedin.com/in/saad-waseem01023776326" target="_blank" class="linkedin-btn">
                                    <i class="fa fa-linkedin"></i>
                                    Connect
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team Member 6 -->
                    <div class="swiper-slide">
                        <div class="team-card">
                            <div class="team-card-image">
                                <img src="{{ asset('assets/images/users/Hussien.webp') }}" alt="Hussien Mousa - Secretary Manager of IADC Suez University Student Chapter">
                            </div>
                            <div class="team-card-content">
                                <div class="team-card-divider"></div>
                                <h4 class="team-card-name">Hussien Mousa</h4>
                                <p class="team-card-role text-primary">Secretary Manager</p>
                                <a href="https://www.linkedin.com/in/hussien-mousa" target="_blank" class="linkedin-btn">
                                    <i class="fa fa-linkedin"></i>
                                    Connect
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="swiper-slide">
                        <div class="team-card">
                            <div class="team-card-image">
                                <img src="{{ asset('assets/images/users/youssif.webp') }}" alt="Youssef Sayed - Operations Manager of IADC Suez University Student Chapter">
                            </div>
                            <div class="team-card-content">
                                <div class="team-card-divider"></div>
                                <h4 class="team-card-name">Youssef Sayed</h4>
                                <p class="team-card-role text-primary">Operations Manager</p>
                                <a href="https://www.linkedin.com/in/youssef-sayed-a5757124b" target="_blank" class="linkedin-btn">
                                    <i class="fa fa-linkedin"></i>
                                    Connect
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide">
                        <div class="team-card">
                            <div class="team-card-image">
                                <img src="{{ asset('assets/images/users/ahmed farouk.webp') }}" alt="Ahmed Farouk - Marketing Manager of IADC Suez University Student Chapter">
                            </div>
                            <div class="team-card-content">
                                <div class="team-card-divider"></div>
                                <h4 class="team-card-name">Ahmed Farouk</h4>
                                <p class="team-card-role text-primary">Markting Manager</p>
                                <a href="https://www.linkedin.com/in/ahmed-farouk172" target="_blank" class="linkedin-btn">
                                    <i class="fa fa-linkedin"></i>
                                    Connect
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide">
                        <div class="team-card">
                            <div class="team-card-image">
                                <img src="{{ asset('assets/images/users/rewan.webp') }}" alt="Rewan Ramadan - HR Manager of IADC Suez University Student Chapter">
                            </div>
                            <div class="team-card-content">
                                <div class="team-card-divider"></div>
                                <h4 class="team-card-name">Rewan Ramadan</h4>
                                <p class="team-card-role text-primary">HR Manager</p>
                                <a href="https://www.linkedin.com/in/rewanramadan" target="_blank" class="linkedin-btn">
                                    <i class="fa fa-linkedin"></i>
                                    Connect
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide">
                        <div class="team-card">
                            <div class="team-card-image">
                                <img src="{{ asset('assets/images/users/fatma.webp') }}" alt="Fatma Amer - Vice Marketing Manager of IADC Suez University Student Chapter">
                            </div>
                            <div class="team-card-content">
                                <div class="team-card-divider"></div>
                                <h4 class="team-card-name">Fatma Amer</h4>
                                <p class="team-card-role text-primary">Vice Markting Manager</p>
                                <a href="https://www.linkedin.com/in/fatma-amer-702217358" target="_blank" class="linkedin-btn">
                                    <i class="fa fa-linkedin"></i>
                                    Connect
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide">
                        <div class="team-card">
                            <div class="team-card-image">
                                <img src="{{ asset('assets/images/users/mamdoh.webp') }}" alt="Mahmoud Mamdouh - Vice Technical Manager of IADC Suez University Student Chapter">
                            </div>
                            <div class="team-card-content">
                                <div class="team-card-divider"></div>
                                <h4 class="team-card-name">Mahmoud Mamdouh</h4>
                                <p class="team-card-role text-primary">Vice Technical Manager</p>
                                <a href="https://www.linkedin.com/in/mahmoud-hamad-788ba62a3" target="_blank" class="linkedin-btn">
                                    <i class="fa fa-linkedin"></i>
                                    Connect
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide">
                        <div class="team-card">
                            <div class="team-card-image">
                                <img src="{{ asset('assets/images/users/ahmed mostafa.webp') }}" alt="Ahmed Mostafa - Vice PR and Treasury Manager of IADC Suez University Student Chapter">
                            </div>
                            <div class="team-card-content">
                                <div class="team-card-divider"></div>
                                <h4 class="team-card-name">Ahmed Mostafa</h4>
                                <p class="team-card-role text-primary">Vice PR & Treasury Manager</p>
                                <a href="https://www.linkedin.com/in/ahmedmostafash" target="_blank" class="linkedin-btn">
                                    <i class="fa fa-linkedin"></i>
                                    Connect
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide">
                        <div class="team-card">
                            <div class="team-card-image">
                                <img src="{{ asset('assets/images/users/mohamed ali.webp') }}" alt="Mohamed Ali - Vice IT Manager of IADC Suez University Student Chapter">
                            </div>
                            <div class="team-card-content">
                                <div class="team-card-divider"></div>
                                <h4 class="team-card-name">Mohamed Ali</h4>
                                <p class="team-card-role text-primary">Vice IT Manager</p>
                                <a href="https://www.linkedin.com/in/mohamed-ali-ofifcial25" target="_blank" class="linkedin-btn">
                                    <i class="fa fa-linkedin"></i>
                                    Connect
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                
                <!-- Pagination dots -->
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Swiper('.team-swiper', {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: false,
        pagination: {
            el: '.team-swiper .swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            576: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
            992: {
                slidesPerView: 3,
                spaceBetween: 30,
            },
            1200: {
                slidesPerView: 3,
                spaceBetween: 30,
            },
        },
        effect: 'slide',
        speed: 600,
        grabCursor: true,
    });
});
</script>