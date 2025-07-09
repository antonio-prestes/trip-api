#!/bin/bash

echo "🛑 Parando o projeto Trip API..."
echo ""

echo "📥 Parando containers Docker..."
docker-compose down

echo "🧹 Removendo volumes (opcional)..."
read -p "Deseja remover os volumes do banco de dados? (y/N): " remove_volumes

if [[ $remove_volumes =~ ^[Yy]$ ]]; then
    echo "🗑️ Removendo volumes..."
    docker-compose down -v
    echo "⚠️ Dados do banco foram removidos!"
else
    echo "💾 Volumes mantidos (dados preservados)"
fi

echo ""
echo "✅ Projeto parado com sucesso!"
echo ""
echo "🚀 Para reiniciar, execute:"
echo "   ./up.sh"
echo ""
