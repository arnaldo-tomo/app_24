#!/bin/bash

# 🚀 Script de Setup Automático - Sistema de Delivery
# Autor: Arnaldo Tomo
# Descrição: Cria toda estrutura de pastas e arquivos necessários

echo "🚀 Iniciando setup do Sistema de Delivery..."
echo "================================================"

# Verificar se está no diretório do Laravel
if [ ! -f "artisan" ]; then
    echo "❌ Erro: Execute este script no diretório raiz do Laravel!"
    exit 1
fi

# Criar estrutura de diretórios
echo "📁 Criando estrutura de diretórios..."

# Diretórios para Controllers
mkdir -p app/Http/Controllers/Admin
mkdir -p app/Http/Controllers/Restaurant
mkdir -p app/Http/Controllers/Api

# Diretórios para Middleware
mkdir -p app/Http/Middleware

# Diretórios para Views
mkdir -p resources/views/admin
mkdir -p resources/views/admin/restaurants
mkdir -p resources/views/admin/orders
mkdir -p resources/views/admin/customers
mkdir -p resources/views/admin/delivery-persons
mkdir -p resources/views/restaurant
mkdir -p resources/views/restaurant/menu
mkdir -p resources/views/restaurant/orders
mkdir -p resources/views/layouts

# Diretórios para Assets
mkdir -p public/storage/restaurants
mkdir -p public/storage/restaurants/covers
mkdir -p public/storage/menu-items
mkdir -p public/storage/avatars
mkdir -p public/storage/categories

# Diretórios para Database
mkdir -p database/seeders

echo "✅ Estrutura de diretórios criada!"

# Função para criar arquivo com conteúdo
create_file() {
    local file_path="$1"
    local content="$2"
    
    # Criar diretório pai se não existir
    mkdir -p "$(dirname "$file_path")"
    
    # Criar arquivo
    echo "$content" > "$file_path"
    echo "📝 Criado: $file_path"
}

# Criar Models
echo "📝 Criando Models..."

# Restaurant Model
create_file "app/Models/Restaurant.php" "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Restaurant extends Model
{
    use HasFactory;

    protected \$fillable = [
        'name', 'slug', 'description', 'phone', 'email', 'address',
        'latitude', 'longitude', 'image', 'cover_image', 'delivery_fee',
        'delivery_time_min', 'delivery_time_max', 'minimum_order',
        'rating', 'total_reviews', 'is_active', 'is_featured',
        'opening_time', 'closing_time', 'working_days', 'user_id'
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'delivery_fee' => 'decimal:2',
            'minimum_order' => 'decimal:2',
            'rating' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'working_days' => 'array',
            'opening_time' => 'datetime:H:i',
            'closing_time' => 'datetime:H:i',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function (\$restaurant) {
            if (empty(\$restaurant->slug)) {
                \$restaurant->slug = Str::slug(\$restaurant->name);
            }
        });
    }

    public function owner()
    {
        return \$this->belongsTo(User::class, 'user_id');
    }

    public function categories()
    {
        return \$this->belongsToMany(Category::class, 'restaurant_categories');
    }

    public function menuCategories()
    {
        return \$this->hasMany(MenuCategory::class);
    }

    public function menuItems()
    {
        return \$this->hasMany(MenuItem::class);
    }

    public function orders()
    {
        return \$this->hasMany(Order::class);
    }

    public function scopeActive(\$query)
    {
        return \$query->where('is_active', true);
    }

    public function scopeFeatured(\$query)
    {
        return \$query->where('is_featured', true);
    }

    public function isOpen()
    {
        \$now = now();
        \$currentDay = strtolower(\$now->format('l'));
        
        if (!in_array(\$currentDay, \$this->working_days)) {
            return false;
        }

        \$currentTime = \$now->format('H:i');
        return \$currentTime >= \$this->opening_time->format('H:i') && 
               \$currentTime <= \$this->closing_time->format('H:i');
    }

    public function getAverageDeliveryTime()
    {
        return (\$this->delivery_time_min + \$this->delivery_time_max) / 2;
    }
}"

# Category Model
create_file "app/Models/Category.php" "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected \$fillable = [
        'name', 'slug', 'image', 'icon', 'is_active', 'sort_order'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function (\$category) {
            if (empty(\$category->slug)) {
                \$category->slug = Str::slug(\$category->name);
            }
        });
    }

    public function restaurants()
    {
        return \$this->belongsToMany(Restaurant::class, 'restaurant_categories');
    }

    public function scopeActive(\$query)
    {
        return \$query->where('is_active', true);
    }
}"

# Comando para gerar outros models
echo "🎨 Gerando Models restantes via Artisan..."
php artisan make:model MenuCategory -m
php artisan make:model MenuItem -m
php artisan make:model Order -m
php artisan make:model OrderItem -m

# Atualizar User Model
echo "👤 Atualizando User Model..."

# Backup do User.php original
cp app/Models/User.php app/Models/User.php.backup

create_file "app/Models/User.php" "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected \$fillable = [
        'name', 'email', 'password', 'role', 'phone', 'address',
        'latitude', 'longitude', 'avatar', 'is_active'
    ];

    protected \$hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_active' => 'boolean',
        ];
    }

    // Role checks
    public function isAdmin()
    {
        return \$this->role === 'admin';
    }

    public function isRestaurantOwner()
    {
        return \$this->role === 'restaurant_owner';
    }

    public function isCustomer()
    {
        return \$this->role === 'customer';
    }

    public function isDeliveryPerson()
    {
        return \$this->role === 'delivery_person';
    }

    // Relationships
    public function restaurants()
    {
        return \$this->hasMany(Restaurant::class);
    }

    public function orders()
    {
        return \$this->hasMany(Order::class);
    }

    public function deliveries()
    {
        return \$this->hasMany(Order::class, 'delivery_person_id');
    }
}"

# Criar Middleware
echo "🛡️ Criando Middleware..."

create_file "app/Http/Middleware/AdminMiddleware.php" "<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request \$request, Closure \$next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isAdmin()) {
            abort(403, 'Acesso negado. Apenas administradores podem acessar esta área.');
        }

        return \$next(\$request);
    }
}"

create_file "app/Http/Middleware/RestaurantOwnerMiddleware.php" "<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestaurantOwnerMiddleware
{
    public function handle(Request \$request, Closure \$next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isRestaurantOwner()) {
            abort(403, 'Acesso negado. Apenas proprietários de restaurantes podem acessar esta área.');
        }

        return \$next(\$request);
    }
}"

# Criar Seeders
echo "🌱 Criando Seeders..."

create_file "database/seeders/UserSeeder.php" "<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        \$admin = User::create([
            'name' => 'Administrador Sistema',
            'email' => 'admin@deliverysystem.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '+258841234567',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Categories
        \$categories = [
            ['name' => 'Pizza', 'icon' => '🍕', 'sort_order' => 1],
            ['name' => 'Hambúrguer', 'icon' => '🍔', 'sort_order' => 2],
            ['name' => 'Frango', 'icon' => '🍗', 'sort_order' => 3],
            ['name' => 'Massa', 'icon' => '🍝', 'sort_order' => 4],
            ['name' => 'Chinesa', 'icon' => '🥡', 'sort_order' => 5],
            ['name' => 'Bebidas', 'icon' => '🥤', 'sort_order' => 6],
            ['name' => 'Sobremesas', 'icon' => '🍰', 'sort_order' => 7],
            ['name' => 'Saudável', 'icon' => '🥗', 'sort_order' => 8],
        ];

        foreach (\$categories as \$category) {
            Category::create(\$category);
        }

        \$this->command->info('Users and categories created successfully!');
    }
}"

# Criar Controllers básicos
echo "🎮 Criando Controllers..."

php artisan make:controller Admin/AdminController
php artisan make:controller Admin/RestaurantController --resource
php artisan make:controller Restaurant/RestaurantDashboardController
php artisan make:controller Restaurant/MenuController
php artisan make:controller Api/AuthController
php artisan make:controller Api/RestaurantController
php artisan make:controller Api/OrderController
php artisan make:controller Api/PaymentController
php artisan make:controller Api/DeliveryController

# Gerar migrations
echo "📊 Gerando Migrations..."

php artisan make:migration create_restaurants_table
php artisan make:migration create_categories_table
php artisan make:migration create_restaurant_categories_table
php artisan make:migration create_menu_categories_table
php artisan make:migration create_menu_items_table
php artisan make:migration create_orders_table
php artisan make:migration create_order_items_table
php artisan make:migration add_role_to_users_table

# Criar arquivo de rotas API básico
echo "🛣️ Criando estrutura de rotas..."

create_file "routes/api_backup.php" "<?php
// Backup das rotas API originais criado em: $(date)
// Cole o conteúdo das rotas da documentação aqui
"

# Criar layout básico
echo "🎨 Criando layouts básicos..."

create_file "resources/views/layouts/admin.blade.php" "<!DOCTYPE html>
<html lang=\"{{ str_replace('_', '-', app()->getLocale()) }}\">
<head>
    <meta charset=\"utf-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
    <meta name=\"csrf-token\" content=\"{{ csrf_token() }}\">
    <title>{{ config('app.name', 'Laravel') }} - Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src=\"https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js\" defer></script>
    <link href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css\" rel=\"stylesheet\">
</head>
<body class=\"font-sans antialiased bg-gray-50\">
    <div class=\"min-h-screen\">
        @yield('content')
    </div>
</body>
</html>"

create_file "resources/views/admin/dashboard.blade.php" "@extends('layouts.admin')

@section('content')
<div class=\"p-6\">
    <h1 class=\"text-2xl font-bold text-gray-900 mb-6\">Dashboard Administrativo</h1>
    
    <div class=\"grid grid-cols-1 md:grid-cols-4 gap-6\">
        <!-- Stats cards aqui -->
        <div class=\"bg-white rounded-lg shadow p-6\">
            <h3 class=\"text-lg font-medium text-gray-900\">Total de Pedidos</h3>
            <p class=\"text-3xl font-bold text-orange-600\">{{ \$stats['total_orders'] ?? 0 }}</p>
        </div>
        
        <div class=\"bg-white rounded-lg shadow p-6\">
            <h3 class=\"text-lg font-medium text-gray-900\">Receita Total</h3>
            <p class=\"text-3xl font-bold text-green-600\">MT {{ number_format(\$stats['total_revenue'] ?? 0, 2) }}</p>
        </div>
        
        <div class=\"bg-white rounded-lg shadow p-6\">
            <h3 class=\"text-lg font-medium text-gray-900\">Restaurantes Ativos</h3>
            <p class=\"text-3xl font-bold text-blue-600\">{{ \$stats['active_restaurants'] ?? 0 }}</p>
        </div>
        
        <div class=\"bg-white rounded-lg shadow p-6\">
            <h3 class=\"text-lg font-medium text-gray-900\">Entregadores Online</h3>
            <p class=\"text-3xl font-bold text-purple-600\">{{ \$stats['online_delivery_persons'] ?? 0 }}</p>
        </div>
    </div>
</div>
@endsection"

# Criar arquivo de configuração
create_file ".env.example.delivery" "# 🚀 Configurações do Sistema de Delivery

# App
APP_NAME=\"Sistema de Delivery\"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=delivery_system
DB_USERNAME=root
DB_PASSWORD=

# Cache
CACHE_DRIVER=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file

# Storage
FILESYSTEM_DISK=public

# M-Pesa (Produção)
MPESA_API_URL=https://api.mpesa.vm.co.mz
MPESA_API_KEY=sua_chave_mpesa
MPESA_PUBLIC_KEY=sua_chave_publica_mpesa

# Mola (Produção)
MOLA_API_URL=https://api.mola.co.mz
MOLA_API_KEY=sua_chave_mola

# Mail
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=\"noreply@deliverysystem.com\"
MAIL_FROM_NAME=\"\${APP_NAME}\"
"

# Criar script de comandos úteis
create_file "scripts/dev-commands.sh" "#!/bin/bash

# 🛠️ Comandos de desenvolvimento úteis

echo \"�� Comandos disponíveis:\"
echo \"1. Resetar banco de dados\"
echo \"2. Executar migrations e seeders\"
echo \"3. Gerar chave da aplicação\"
echo \"4. Limpar caches\"
echo \"5. Criar link do storage\"
echo \"6. Instalar dependências\"
echo \"7. Executar servidor\"

read -p \"Escolha uma opção (1-7): \" option

case \$option in
    1)
        echo \"🔄 Resetando banco de dados...\"
        php artisan migrate:reset
        php artisan migrate
        php artisan db:seed
        ;;
    2)
        echo \"📊 Executando migrations e seeders...\"
        php artisan migrate
        php artisan db:seed
        ;;
    3)
        echo \"🔑 Gerando chave da aplicação...\"
        php artisan key:generate
        ;;
    4)
        echo \"🧹 Limpando caches...\"
        php artisan cache:clear
        php artisan config:clear
        php artisan route:clear
        php artisan view:clear
        ;;
    5)
        echo \"🔗 Criando link do storage...\"
        php artisan storage:link
        ;;
    6)
        echo \"📦 Instalando dependências...\"
        composer install
        npm install
        ;;
    7)
        echo \"🚀 Executando servidor...\"
        php artisan serve
        ;;
    *)
        echo \"❌ Opção inválida!\"
        ;;
esac
"

chmod +x scripts/dev-commands.sh

# Criar README personalizado
create_file "README_DELIVERY.md" "# 🚀 Sistema de Delivery - Moçambique

## 📋 Sobre o Projeto

Sistema completo de delivery similar ao Uber Eats, desenvolvido para o mercado moçambicano com integração M-Pesa e Mola.

## 🛠️ Instalação Rápida

1. **Configurar ambiente:**
   \`\`\`bash
   cp .env.example.delivery .env
   php artisan key:generate
   \`\`\`

2. **Instalar dependências:**
   \`\`\`bash
   composer install
   npm install && npm run build
   \`\`\`

3. **Configurar banco:**
   \`\`\`bash
   php artisan migrate
   php artisan db:seed
   php artisan storage:link
   \`\`\`

4. **Executar:**
   \`\`\`bash
   php artisan serve
   \`\`\`

## 🔐 Credenciais Padrão

- **Admin:** admin@deliverysystem.com / password123
- **Restaurante:** joao@bellavista.com / password123
- **Entregador:** carlos@entregador.com / password123
- **Cliente:** maria@cliente.com / password123

## 🚀 URLs Importantes

- **Dashboard Admin:** http://localhost:8000/admin/dashboard
- **Painel Restaurante:** http://localhost:8000/restaurant/dashboard
- **API Base:** http://localhost:8000/api/v1

## 📱 Testando a API

\`\`\`bash
# Login
curl -X POST http://localhost:8000/api/v1/auth/login \\
     -H \"Content-Type: application/json\" \\
     -d '{\"email\":\"maria@cliente.com\",\"password\":\"password123\"}'

# Listar restaurantes
curl -X GET http://localhost:8000/api/v1/restaurants \\
     -H \"Accept: application/json\"
\`\`\`

## 🛠️ Scripts Úteis

Execute \`./scripts/dev-commands.sh\` para comandos de desenvolvimento.

## 📞 Suporte

Desenvolvido por Arnaldo Tomo - Moçambique 🇲🇿
"

echo ""
echo "🎉 Setup concluído com sucesso!"
echo "================================================"
echo ""
echo "📋 Próximos passos:"
echo "1. Configurar .env (copie de .env.example.delivery)"
echo "2. Executar: php artisan key:generate"
echo "3. Configurar banco de dados no .env"
echo "4. Executar: php artisan migrate"
echo "5. Executar: php artisan db:seed"
echo "6. Executar: php artisan storage:link"
echo "7. Executar: php artisan serve"
echo ""
echo "🚀 Para comandos úteis, execute: ./scripts/dev-commands.sh"
echo ""
echo "✅ Sistema pronto para desenvolvimento!"




