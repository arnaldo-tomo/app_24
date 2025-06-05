<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Peça suas refeições favoritas com facilidade no Meu24. Explore restaurantes locais, personalize seu pedido e receba entrega rápida e confiável na sua porta. Baixe agora para uma experiência gastronômica prática e deliciosa!">
  <title>Meu24</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="website/css/icofont.min.css">
  <link rel="stylesheet" href="website/css/owl.carousel.min.css">
  <link rel="stylesheet" href="website/css/bootstrap.min.css">
  <link rel="stylesheet" href="website/css/aos.css">
  <link rel="stylesheet" href="website/css/style.css">
  <link rel="stylesheet" href="website/css/responsive.css">
  <link rel="shortcut icon" href="website/images/favicon.webp" type="image/x-icon">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
</head>

<body>
  <div id="preloader">
    <div id="loader"></div>
  </div>

  <header>
    <div class="container">
      <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="#">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #fb923c, #ec4899);">
              <i class="fas fa-utensils" style="font-size: 18px; color: white;"></i>
            </div>
            <h1 style="font-size: 20px; font-weight: bold; color: #111827; margin: 0;">Meu24</h1>
          </div>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon">
            <span class="toggle-wrap">
              <span class="toggle-bar"></span>
            </span>
          </span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="ml-auto navbar-nav">
            <li class="nav-item has_dropdown">
              <a class="nav-link" href="#">Início</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="website/about.html">Sobre Nós</a>
            </li>
            <li class="nav-item has_dropdown">
              <a class="nav-link" href="#">Páginas</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="website/reviews.html">Avaliações</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="website/blog-list.html">Blog</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="website/contact.html">Contato</a>
            </li>
            <li class="nav-item">
              <div class="btn_block">
                <a class="nav-link dark_btn" href="website/contact.html">Teste Grátis por 7 Dias</a>
                <div class="btn_bottom"></div>
              </div>
            </li>
          </ul>
        </div>
      </nav>
    </div>
  </header>

  <section class="banner_section" id="home_sec">
    <div class="hero_side_element left_side"> <img src="website/images/hero_element_1.webp" alt="imagem"> </div>
    <div class="hero_side_element right_side"> <img src="website/images/hero_element_2.webp" alt="imagem"> </div>
    <div class="container">
      <div class="row">
        <div class="col-md-12" data-aos="fade-up" data-aos-duration="1500">
          <div class="banner_text">
            <h1>Entrega de comida rápida na sua cidade!</h1>
            <p>Comprometemo-nos a entregar sua comida em até 30 minutos. Se não conseguirmos, a entrega é por nossa conta!</p>
            <span class="trial_txt"> <strong>Entrega grátis nos primeiros 5 pedidos!</strong> </span>
          </div>
          <ul class="app_btn">
            <li>
              <a href="#">
                <img class="blue_img" src="website/images/googleplay.webp" alt="Google Play">
              </a>
            </li>
            <li>
              <a href="#">
                <img class="blue_img" src="website/images/appstorebtn.webp" alt="App Store">
              </a>
            </li>
          </ul>
        </div>
        <div class="col-md-12">
          <div class="hero_img">
            <div class="desktop">
              <img src="website/images/hero_image.webp" alt="Imagem Principal">
            </div>
            <div class="mobile_view">
              <img src="website/images/hero_image.webp" alt="Imagem Principal">
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="page_wrapper">
    <section class="row_am usp_section">
      <div class="blure_shape bs_1"></div>
      <div class="blure_shape bs_2"></div>
      <div class="inner_sec" id="counter">
        <div class="container">
          <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6">
              <div class="usp_box">
                <div class="usp_icon"><img src="website/images/usp1.webp" alt="Usuários Felizes"></div>
                <div class="usp_text">
                  <span class="counter-value" data-count="5000">5000</span><span>+</span>
                  <p>Usuários Felizes</p>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
              <div class="usp_box">
                <div class="usp_icon"><img src="website/images/usp2.webp" alt="Avaliações Positivas"></div>
                <div class="usp_text">
                  <span class="counter-value" data-count="1879">1879</span><span>+</span>
                  <p>Avaliações Positivas</p>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
              <div class="usp_box">
                <div class="usp_icon"><img src="website/images/usp3.webp" alt="Restaurantes Cadastrados"></div>
                <div class="usp_text">
                  <span class="counter-value" data-count="3855">3855</span><span>+</span>
                  <p>Restaurantes Cadastrados</p>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
              <div class="usp_box">
                <div class="usp_icon"><img src="website/images/usp4.webp" alt="Entregas Bem-Sucedidas"></div>
                <div class="usp_text">
                  <span class="counter-value" data-count="985">985</span><span>M+</span>
                  <p>Entregas Bem-Sucedidas</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="row_am features_section" id="why_sec">
      <div class="container">
        <div class="section_title" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="100">
          <span class="title_badge">Por que o Meu24?</span>
          <h2>Por que escolher o Meu24</h2>
          <p>Peça sua comida favorita de forma rápida e prática, com os melhores restaurantes da sua região.</p>
        </div>
        <div class="feature_detail">
          <div class="float_element lft_side"> <img src="website/images/food1.webp" alt="Comida 1"> </div>
          <div class="float_element rht_side"> <img src="website/images/food2.webp" alt="Comida 2"> </div>
          <div class="float_element btm_side"> <img src="website/images/food3.webp" alt="Comida 3"> </div>
          <div class="left_data feature_box">
            <div class="data_block color1" data-aos="fade-right" data-aos-duration="1500">
              <div class="icon">
                <img src="website/images/whyicon1.webp" alt="Entrega Rápida">
              </div>
              <div class="text">
                <h6>Entrega em 30 minutos</h6>
                <p>Receba suas refeições favoritas em até 30 minutos, quentinhas e frescas!</p>
              </div>
            </div>
            <div class="data_block color2" data-aos="fade-right" data-aos-duration="1500">
              <div class="icon">
                <img src="website/images/whyicon2.webp" alt="Comida de Qualidade">
              </div>
              <div class="text">
                <h6>Comida de Qualidade</h6>
                <p>Desfrute de refeições de alta qualidade, preparadas pelos melhores restaurantes locais.</p>
              </div>
            </div>
          </div>
          <div class="right_data feature_box">
            <div class="data_block color3" data-aos="fade-left" data-aos-duration="1500">
              <div class="icon">
                <img src="website/images/whyicon3.webp" alt="Mapa em Tempo Real">
              </div>
              <div class="text">
                <h6>Mapa em Tempo Real</h6>
                <p>Acompanhe seu pedido em tempo real com nosso recurso de mapa ao vivo!</p>
              </div>
            </div>
            <div class="data_block color4" data-aos="fade-left" data-aos-duration="1500">
              <div class="icon">
                <img src="website/images/whyicon4.webp" alt="Suporte 24/7">
              </div>
              <div class="text">
                <h6>Suporte 24/7</h6>
                <p>Conte com nossa equipe de suporte disponível 24 horas por dia, 7 dias por semana!</p>
              </div>
            </div>
          </div>
          <div class="feature_img" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="100">
            <img src="website/images/features_frame.webp" alt="Imagem de Recursos">
          </div>
        </div>
      </div>
    </section>

    <section class="row_am dishes_section">
      <div class="container">
        <div class="section_title" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="100">
          <span class="title_badge">Pratos Deliciosos!</span>
          <h2>Acesse mais de 1000 pratos com um toque</h2>
          <p>Explore uma variedade incrível de opções gastronômicas dos melhores restaurantes da sua cidade.</p>
        </div>
      </div>
      <div class="dish_slider" data-aos="fade-in" data-aos-duration="1500">
        <div class="owl-carousel owl-theme" id="about_slider">
          <div class="item">
            <div class="dish_slides">
              <img src="website/images/dish1.webp" alt="Prato 1">
            </div>
          </div>
          <div class="item">
            <div class="dish_slides">
              <img src="website/images/dish2.webp" alt="Prato 2">
            </div>
          </div>
          <div class="item">
            <div class="dish_slides">
              <img src="website/images/dish3.webp" alt="Prato 3">
            </div>
          </div>
          <div class="item">
            <div class="dish_slides">
              <img src="website/images/dish4.webp" alt="Prato 4">
            </div>
          </div>
          <div class="item">
            <div class="dish_slides">
              <img src="website/images/dish5.webp" alt="Prato 5">
            </div>
          </div>
          <div class="item">
            <div class="dish_slides">
              <img src="website/images/dish6.webp" alt="Prato 6">
            </div>
          </div>
          <div class="item">
            <div class="dish_slides">
              <img src="website/images/dish7.webp" alt="Prato 7">
            </div>
          </div>
          <div class="item">
            <div class="dish_slides">
              <img src="website/images/dish8.webp" alt="Prato 8">
            </div>
          </div>
          <div class="item">
            <div class="dish_slides">
              <img src="website/images/dish9.webp" alt="Prato 9">
            </div>
          </div>
          <div class="item">
            <div class="dish_slides">
              <img src="website/images/dish10.webp" alt="Prato 10">
            </div>
          </div>
        </div>
      </div>
      <div class="ctr_app_btn_block">
        <p><strong>Entrega grátis nos primeiros 5 pedidos!</strong></p>
        <ul class="app_btn">
          <li>
            <a href="#">
              <img class="blue_img" src="website/images/googleplay.webp" alt="Google Play">
            </a>
          </li>
          <li>
            <a href="#">
              <img class="blue_img" src="website/images/appstorebtn.webp" alt="App Store">
            </a>
          </li>
        </ul>
      </div>
    </section>

    <section class="row_am our_client">
      <div class="container">
        <div class="section_title" data-aos="fade-up" data-aos-duration="1500">
          <span class="title_badge">Nossos Parceiros</span>
          <h2>Confiado por mais de 2.500 restaurantes</h2>
        </div>
        <ul class="client_list">
          <li>
            <div class="client_logo" data-aos="fade-up" data-aos-duration="1500">
              <img src="website/images/res1.webp" alt="Restaurante 1">
            </div>
          </li>
          <li>
            <div class="client_logo" data-aos="fade-up" data-aos-duration="1500">
              <img src="website/images/res2.webp" alt="Restaurante 2">
            </div>
          </li>
          <li>
            <div class="client_logo" data-aos="fade-up" data-aos-duration="1500">
              <img src="website/images/res3.webp" alt="Restaurante 3">
            </div>
          </li>
          <li>
            <div class="client_logo" data-aos="fade-up" data-aos-duration="1500">
              <img src="website/images/res4.webp" alt="Restaurante 4">
            </div>
          </li>
          <li>
            <div class="client_logo" data-aos="fade-up" data-aos-duration="1500">
              <img src="website/images/res5.webp" alt="Restaurante 5">
            </div>
          </li>
          <li>
            <div class="client_logo" data-aos="fade-up" data-aos-duration="1500">
              <img src="website/images/res6.webp" alt="Restaurante 6">
            </div>
          </li>
          <li>
            <div class="client_logo" data-aos="fade-up" data-aos-duration="1500">
              <img src="website/images/res7.webp" alt="Restaurante 7">
            </div>
          </li>
          <li>
            <div class="client_logo" data-aos="fade-up" data-aos-duration="1500">
              <img src="website/images/res8.webp" alt="Restaurante 8">
            </div>
          </li>
        </ul>
        <div class="ctr_cta">
          <div class="btn_block">
            <a href="website/blog-detail.html" class="ml-0 btn puprple_btn">Cadastre seu Restaurante</a>
          </div>
        </div>
      </div>
    </section>

    <section class="winwin_section row_am" id="benefits_sec">
      <div class="container">
        <div class="section_title">
          <span class="title_badge">Benefícios</span>
          <h2>Vantagens para restaurantes e clientes</h2>
          <p>Uma plataforma que conecta restaurantes e clientes, oferecendo praticidade e eficiência para todos.</p>
        </div>
        <div class="win_listing">
          <div class="row">
            <div class="col-md-12">
              <div class="listing_inner">
                <div class="win_block" data-aos="fade-up" data-aos-duration="1500">
                  <div class="img">
                    <img src="website/images/win1.webp" alt="Gestão de Restaurantes">
                  </div>
                  <div class="text">
                    <h4>Gestão simplificada para restaurantes</h4>
                    <p>Gerencie pedidos, conecte-se ao sistema de vendas e receba pagamentos antecipados com facilidade.</p>
                    <ul class="win_list">
                      <li>
                        <div class="icon">
                          <span><i class="icofont-check-circled"></i></span>
                        </div>
                        <div class="li_text">
                          <p>Gerenciamento de pedidos</p>
                        </div>
                      </li>
                      <li>
                        <div class="icon">
                          <span><i class="icofont-check-circled"></i></span>
                        </div>
                        <div class="li_text">
                          <p>Conexão com sistemas de vendas</p>
                        </div>
                      </li>
                      <li>
                        <div class="icon">
                          <span><i class="icofont-check-circled"></i></span>
                        </div>
                        <div class="li_text">
                          <p>Pagamentos antecipados</p>
                        </div>
                      </li>
                      <li>
                        <div class="icon">
                          <span><i class="icofont-check-circled"></i></span>
                        </div>
                        <div class="li_text">
                          <p>Mais pedidos</p>
                        </div>
                      </li>
                    </ul>
                    <div class="btn_block">
                      <a href="#" class="ml-0 btn puprple_btn">Cadastre seu Restaurante</a>
                    </div>
                  </div>
                </div>
                <div class="win_block" data-aos="fade-up" data-aos-duration="1500">
                  <div class="img">
                    <img src="website/images/win2.webp" alt="Pedidos Fáceis">
                  </div>
                  <div class="text">
                    <h4>Pedidos simples e rápidos para clientes</h4>
                    <p>Peça sua comida favorita em poucos cliques e receba onde estiver, com total comodidade.</p>
                    <ul class="win_list">
                      <li>
                        <div class="icon">
                          <span><i class="icofont-check-circled"></i></span>
                        </div>
                        <div class="li_text">
                          <p>Gerenciamento de pedidos</p>
                        </div>
                      </li>
                      <li>
                        <div class="icon">
                          <span><i class="icofont-check-circled"></i></span>
                        </div>
                        <div class="li_text">
                          <p>Conexão com sistemas de vendas</p>
                        </div>
                      </li>
                      <li>
                        <div class="icon">
                          <span><i class="icofont-check-circled"></i></span>
                        </div>
                        <div class="li_text">
                          <p>Pagamentos antecipados</p>
                        </div>
                      </li>
                      <li>
                        <div class="icon">
                          <span><i class="icofont-check-circled"></i></span>
                        </div>
                        <div class="li_text">
                          <p>Mais pedidos</p>
                        </div>
                      </li>
                    </ul>
                    <div class="btn_block">
                      <a href="#" class="ml-0 btn puprple_btn">Baixe o App para Pedir</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="advance_feature_section row_am">
      <div class="af_innner">
        <div class="float_element lft_side"> <img src="website/images/foodA.webp" alt="Comida A"> </div>
        <div class="float_element rht_side"> <img src="website/images/foodB.webp" alt="Comida B"> </div>
        <div class="float_element btm_side"> <img src="website/images/foodC.webp" alt="Comida C"> </div>
        <div class="container">
          <div class="section_title">
            <span class="title_badge">Passos Simples</span>
            <h2>Como Funciona</h2>
            <p>Peça sua comida favorita em poucos passos e receba diretamente na sua porta!</p>
          </div>
          <div class="af_listing">
            <div class="row">
              <div class="col-md-12">
                <div class="listing_inner">
                  <div class="af_block" data-aos="fade-up" data-aos-duration="1500">
                    <div class="img">
                      <img src="website/images/how1.webp" alt="Baixe o App">
                    </div>
                    <div class="text">
                      <h5>Baixe o app e crie uma conta grátis</h5>
                      <p>Comece baixando o Meu24 e criando sua conta em poucos minutos.</p>
                    </div>
                    <div class="process_num">01</div>
                  </div>
                  <div class="af_block" data-aos="fade-up" data-aos-duration="1500">
                    <div class="img">
                      <img src="website/images/how2.webp" alt="Faça seu Pedido">
                    </div>
                    <div class="text">
                      <h5>Faça pedidos no seu restaurante favorito</h5>
                      <p>Escolha entre milhares de opções e personalize seu pedido com facilidade.</p>
                    </div>
                    <div class="process_num">02</div>
                  </div>
                  <div class="af_block" data-aos="fade-up" data-aos-duration="1500">
                    <div class="img">
                      <img src="website/images/how3.webp" alt="Receba em Casa">
                    </div>
                    <div class="text">
                      <h5>Receba diretamente na sua casa, sem complicações</h5>
                      <p>Acompanhe seu pedido em tempo real e receba sua comida quentinha!</p>
                    </div>
                    <div class="process_num">03</div>
                  </div>
                </div>
                <div class="ctr_app_btn_block">
                  <p><strong>50% de desconto no seu primeiro pedido! Aproveite agora.</strong></p>
                  <ul class="app_btn">
                    <li>
                      <a href="#">
                        <img class="blue_img" src="website/images/googleplay.webp" alt="Google Play">
                      </a>
                    </li>
                    <li>
                      <a href="#">
                        <img class="blue_img" src="website/images/appstorebtn.webp" alt="App Store">
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="key_feature_section row_am" id="reviews_sec">
      <div class="kf_side_element left_side"> <img src="website/images/thumbup.webp" alt="Polegar para Cima"> </div>
      <div class="kf_side_element right_side"> <img src="website/images/like.webp" alt="Curtir"> </div>
      <div class="key_innner">
        <div class="container">
          <div class="section_title">
            <span class="title_badge">Depoimentos</span>
            <h2>Nossos clientes felizes</h2>
            <p>Veja o que nossos usuários estão dizendo sobre a experiência com o Meu24!</p>
          </div>
          <div id="feature_slider" class="owl-carousel owl-theme" data-aos="fade-up" data-aos-duration="1500">
            <div class="item">
              <div class="feature_box">
                <div class="img">
                  <img src="website/images/story1.webp" alt="Olivia Sam">
                </div>
                <div class="txt_blk">
                  <h6>Olivia Sam</h6>
                  <div class="rating">
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                  </div>
                  <p><span class="story_bold">"Entrega pontual em todos os pedidos!"</span> A experiência com o Meu24 é incrível, sempre recebo minha comida no prazo!</p>
                </div>
                <div class="quote_img">
                  <img src="website/images/quote.webp" alt="Citação">
                </div>
              </div>
            </div>
            <div class="item">
              <div class="feature_box">
                <div class="img">
                  <img src="website/images/story2.webp" alt="Sandra Luna">
                </div>
                <div class="txt_blk">
                  <h6>Sandra Luna</h6>
                  <div class="rating">
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                  </div>
                  <p><span class="story_bold">"Comida de qualidade e saudável"</span> Adoro a variedade de opções saudáveis no Meu24!</p>
                </div>
                <div class="quote_img">
                  <img src="website/images/quote.webp" alt="Citação">
                </div>
              </div>
            </div>
            <div class="item">
              <div class="feature_box">
                <div class="img">
                  <img src="website/images/story3.webp" alt="Amelia Elisa">
                </div>
                <div class="txt_blk">
                  <h6>Amelia Elisa</h6>
                  <div class="rating">
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                  </div>
                  <p><span class="story_bold">"App fácil de usar, muito útil!"</span> O Meu24 torna pedir comida algo simples e rápido!</p>
                </div>
                <div class="quote_img">
                  <img src="website/images/quote.webp" alt="Citação">
                </div>
              </div>
            </div>
            <div class="item">
              <div class="feature_box">
                <div class="img">
                  <img src="website/images/story4.webp" alt="Maria Sim">
                </div>
                <div class="txt_blk">
                  <h6>Maria Sim</h6>
                  <div class="rating">
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                    <span><i class="icofont-star"></i></span>
                  </div>
                  <p><span class="story_bold">"Equipe de suporte incrível!"</span> Sempre que precisei, o suporte do Meu24 foi rápido e eficiente!</p>
                </div>
                <div class="quote_img">
                  <img src="website/images/quote.webp" alt="Citação">
                </div>
              </div>
            </div>
          </div>
          <div class="ctr_cta">
            <div class="btn_block">
              <a href="website/blog-detail.html" class="ml-0 btn puprple_btn">Leia Mais Depoimentos</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="row_am download_app" id="download_sec">
      <div class="task_block" data-aos="fade-up" data-aos-duration="1500">
        <div class="blure_shape bs_1"></div>
        <div class="blure_shape bs_2"></div>
        <div class="row">
          <div class="col-md-6">
            <div class="task_text">
              <div class="section_title white_text" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="100">
                <span class="title_badge">Baixe Agora</span>
                <h2>Baixe o app e experimente mais de 4500 pratos</h2>
                <p>Descubra uma infinidade de opções gastronômicas com o Meu24, tudo ao alcance de um toque!</p>
              </div>
              <ul class="app_btn" data-aos="fade-up" data-aos-duration="1500">
                <li>
                  <a href="#">
                    <img class="blue_img" src="website/images/black_google_play.webp" alt="Google Play">
                  </a>
                </li>
                <li>
                  <a href="#">
                    <img class="blue_img" src="website/images/black_appstore.webp" alt="App Store">
                  </a>
                </li>
              </ul>
            </div>
          </div>
          <div class="col-md-6">
            <div class="task_img" data-aos="fade-in" data-aos-duration="1500">
              <div class="frame_img">
                <img src="website/images/our_app.webp" alt="Aplicativo Meu24">
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="cta_section new white_text" id="support_sec">
      <div class="container">
        <div class="cta_box">
          <div class="element">
            <span class="element1"> <img src="website/images/element_white_3.webp" alt="Elemento 1"> </span>
            <span class="element2"> <img src="website/images/element_white_4.webp" alt="Elemento 2"> </span>
          </div>
          <div class="left">
            <div class="section_title" data-aos="fade-in" data-aos-duration="1500" data-aos-delay="100">
              <img src="website/images/customer-icon.webp" class="customer_icon" alt="Ícone de Suporte">
              <h3>Precisa de suporte?</h3>
              <p>Estamos aqui para ajudar você a qualquer hora!</p>
            </div>
          </div>
          <div class="right">
            <div class="btn_block">
              <a href="tel:123-456-7890" class="btn puprple_btn aos-init aos-animate call_btn"><i class="icofont-ui-call"></i> Ligue Agora</a>
              <a href="mailto:suporte@meu24.com.br" class="btn aos-init aos-animate email_btn"><i class="icofont-envelope-open"></i> Envie um E-mail</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <footer>
      <div class="top_footer" id="contact">
        <div class="container">
          <div class="row">
            <div class="col-lg-5 col-md-6 col-12">
              <div class="abt_side">
                <div class="logo"> <img src="website/images/logo.webp" alt="Logo Meu24"> </div>
                <p>O Meu24 é sua plataforma de delivery de comida, conectando você aos melhores restaurantes da sua cidade com praticidade e rapidez.</p>
                <ul class="app_btn">
                  <li>
                    <a href="#">
                      <img src="website/images/appstorebtn.webp" alt="App Store">
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <img src="website/images/googleplay.webp" alt="Google Play">
                    </a>
                  </li>
                </ul>
              </div>
            </div>
            <div class="col-lg-2 col-md-6 col-12">
              <div class="links">
                <h5>Links Rápidos</h5>
                <ul>
                  <li><a href="website/index.html">Início</a></li>
                  <li><a href="website/about.html">Sobre Nós</a></li>
                  <li><a href="website/pricing.html">Preços</a></li>
                  <li><a href="website/blog-list.html">Blog</a></li>
                  <li><a href="website/contact.html">Contato</a></li>
                </ul>
              </div>
            </div>
            <div class="col-lg-2 col-md-6 col-12">
              <div class="links">
                <h5>Suporte</h5>
                <ul>
                  <li><a href="#">Perguntas Frequentes</a></li>
                  <li><a href="#">Suporte</a></li>
                  <li><a href="#">Como Funciona</a></li>
                  <li><a href="#">Termos e Condições</a></li>
                  <li><a href="#">Política de Privacidade</a></li>
                </ul>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
              <h5>Inscreva-se</h5>
              <div class="news_letter">
                <p>Assine nossa newsletter para receber atualizações e promoções exclusivas!</p>
                <form>
                  <div class="form-group">
                    <input type="email" class="form-control" placeholder="Digite seu e-mail">
                    <button class="btn" aria-label="inscrever-se"><i class="icofont-paper-plane"></i></button>
                  </div>
                  <p class="note">Ao clicar em enviar, você concorda em receber mensagens.</p>
                </form>
              </div>
            </div>
          </div>
        </div>
        <div class="bottom_footer">
          <div class="container">
            <div class="row">
              <div class="col-md-4">
                <p>© Direitos Reservados 2024.</p>
              </div>
              <div class="col-md-4">
                <ul class="social_media">
                  <li><a href="https://facebook.com/meu24" aria-label="Página do Facebook"><i class="icofont-facebook"></i></a></li>
                  <li><a href="https://twitter.com/meu24" aria-label="Página do Twitter"><i class="icofont-twitter"></i></a></li>
                  <li><a href="https://instagram.com/meu24" aria-label="Página do Instagram"><i class="icofont-instagram"></i></a></li>
                  <li><a href="https://pinterest.com/meu24" aria-label="Página do Pinterest"><i class="icofont-pinterest"></i></a></li>
                </ul>
              </div>
              <div class="col-md-4">
                <p class="developer_text">Desenvolvido por <a href="https://themeforest.net/user/kalanidhithemes/portfolio" target="_blank">Kalanidhi Themes</a></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <div class="go_top" id="Gotop">
      <span><i class="icofont-arrow-up"></i></span>
    </div>

    <div class="modal fade youtube-video" id="myModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <button id="close-video" type="button" class="text-right button btn btn-default" data-dismiss="modal">
            <i class="icofont-close-line-circled"></i>
          </button>
          <div class="modal-body">
            <div id="video-container" class="video-container">
              <iframe id="youtubevideo" width="640" height="360" allowfullscreen></iframe>
            </div>
          </div>
          <div class="modal-footer"></div>
        </div>
      </div>
    </div>

  </div>

  <script src="website/js/jquery.js"></script>
  <script src="website/js/owl.carousel.min.js"></script>
  <script src="website/js/bootstrap.min.js"></script>
  <script src="website/js/aos.js"></script>
  <script src="website/js/typed.min.js"></script>
  <script src="website/js/main.js"></script>

  <script>
    $(document).ready(function () {
      let cardBlock = document.querySelectorAll('.task_block');
      let topStyle = 120;
      cardBlock.forEach((card) => {
        card.style.top = `${topStyle}px`;
        topStyle += 30;
      });
    });

    $(document).ready(function () {
      $('#scrollButton').click(function () {
        $('html, body').animate({ scrollTop: $(window).scrollTop() + 600 }, 800);
      });
    });
  </script>
</body>
</html>