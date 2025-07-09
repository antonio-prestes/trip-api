#!/bin/bash

echo "🚀 Configurando o projeto Trip API..."
echo ""

# Verificar se Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker não está rodando. Por favor, inicie o Docker primeiro."
    exit 1
fi

echo "📄 Copiando arquivo .env..."
cp .env.example .env

echo "🐳 Subindo containers Docker..."
docker-compose up -d

echo "⏳ Aguardando containers iniciarem..."
sleep 10

echo "📦 Instalando dependências com Composer..."
docker exec trip_laravel_app composer install

echo "🔑 Gerando chave da aplicação..."
docker exec trip_laravel_app php artisan key:generate

echo "🗄️ Executando migrations e seeders..."
docker exec trip_laravel_app php artisan migrate --seed

echo "📖 Gerando documentação Swagger..."
docker exec trip_laravel_app php artisan l5-swagger:generate 2>/dev/null || echo "⚠️ Aviso: Swagger não configurado"

echo ""
echo "✅ Projeto configurado com sucesso!"
echo ""
echo "🌐 Links úteis:"
echo "   - API: http://localhost:8000"
echo "   - Documentação: http://localhost:8000/api/documentation"
echo ""
echo "🧪 Para executar testes:"
echo "   docker exec trip_laravel_app php artisan test"
echo ""
echo "📝 Para ver logs:"
echo "   docker logs trip_laravel_app"
echo ""
