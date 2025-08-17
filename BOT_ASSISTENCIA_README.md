# Bot de Assistência Técnica - WhatsApp

## Descrição
Bot de WhatsApp para atendimento de assistência técnica usando a API Evolution. O bot oferece um menu interativo para diferentes tipos de aparelhos e direciona automaticamente para atendimento humano quando necessário.

## Funcionalidades

### 🤖 Fluxo Automatizado
1. **Saudação inicial**: "Bom dia! Para agilizar o atendimento, escolha uma das opções:"
2. **Menu de aparelhos**:
   - Geladeira ❄️
   - Microondas 📡  
   - Freezer 🧊
   - Máquina de Lavar 👕

3. **Submenu específico por aparelho** com problemas comuns
4. **Coleta de informações** e direcionamento para técnico especializado

### 🔧 Detecção Inteligente
- **Mensagens de áudio**: Automaticamente direcionadas para atendimento humano
- **Mensagens enviadas por você**: Marcadas como "eu_iniciei" 
- **Verificação de horário**: Só funciona nos horários configurados
- **Controle de erros**: Após 2 tentativas inválidas, direciona para humano

## Rotas Disponíveis

### Webhooks
```
POST /events/bot - Receber eventos da Evolution API
POST /webhook/bot-assistencia - Webhook alternativo
POST /cron/bot/eventos - Para processamento via cron job
```

## Configuração na Evolution API

Configure o webhook da sua instância Evolution para apontar para:
```
https://seudominio.com/events/bot
```

## Estrutura do Banco de Dados

### Tabela: chats
- `jid`: Número do WhatsApp
- `session_id`: ID do dispositivo/sessão
- `await_answer`: Estado atual do chat
- `flow_stage`: Tipo de aparelho selecionado
- `erro`: Contador de erros

### Estados do Chat (`await_answer`)
- `init_chat`: Início da conversa
- `menu_aparelhos`: Aguardando seleção do aparelho
- `menu2`: Aguardando problema específico
- `await_human`: Aguardando atendimento humano
- `eu_iniciei`: Conversa iniciada por você

## Como Testar

### 1. Via Command Artisan
```bash
php artisan bot:test 5511999999999 "Olá" teste_session
```

### 2. Simulação de Fluxo Completo
```bash
# Iniciar conversa
php artisan bot:test 5511999999999 "Oi" 

# Escolher geladeira
php artisan bot:test 5511999999999 "1"

# Escolher problema
php artisan bot:test 5511999999999 "1"
```

### 3. Via Postman/Curl
```bash
curl -X POST http://localhost/events/bot \
-H "Content-Type: application/json" \
-d '{
  "data": {
    "sessionId": "BOT_TESTE",
    "event": "MESSAGE_CREATED", 
    "message": {
      "from": "5511999999999@s.whatsapp.net",
      "fromMe": false,
      "fromGroup": false,
      "text": "Olá",
      "type": "text"
    }
  }
}'
```

## Logs e Debug

Os eventos são logados em:
```
storage/logs/laravel.log
```

Para debug adicional, descomente as linhas no EventsController:
```php
error_log('Cron Event: ' . json_encode($reponseArray));
```

## Personalizações

### Alterar Mensagens
Edite as mensagens no método `verifyService()` do `EventsController.php`:

```php
// Linha ~147 - Mensagem inicial
$text = 'Bom dia! 🌅 Para agilizar o atendimento, escolha uma das opções abaixo:';

// Linhas ~160+ - Opções de problemas por aparelho
$options = [
    "Não está gelando",
    "Fazendo muito barulho", 
    "Vazando água",
    "Não liga"
];
```

### Adicionar Novos Aparelhos
1. Adicione a opção no array inicial (linha ~150)
2. Adicione um novo case no switch (linha ~160+)
3. Configure os problemas específicos

### Alterar Horários de Funcionamento
Configure na tabela `available_slots_config` ou via interface admin.

## Arquivos Principais

- `app/Http/Controllers/EventsController.php` - Lógica principal do bot
- `app/Http/Controllers/WebhookController.php` - Webhooks
- `app/Models/Chat.php` - Model do chat
- `app/Models/Device.php` - Model dos dispositivos
- `routes/web.php` - Rotas

## Próximos Passos

1. ✅ Bot básico funcionando
2. ⏳ Interface admin para gerenciar chats ativos  
3. ⏳ Relatórios de atendimento
4. ⏳ Integração com sistema de OS (Ordem de Serviço)
5. ⏳ Bot multilíngue
6. ⏳ Integração com calendário para agendamentos

## Suporte

Para dúvidas ou problemas:
1. Verifique os logs em `storage/logs/laravel.log`
2. Teste via command: `php artisan bot:test`
3. Verifique se a Evolution API está respondendo
4. Confirme se os webhooks estão configurados corretamente
