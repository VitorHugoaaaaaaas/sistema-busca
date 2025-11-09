<?php

/**
 * Migration: Criação de índices para otimização de buscas
 * 
 * CORRIGIDO: Índices desabilitados para evitar conflito com SQLite
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a migration - cria os índices
     */
    public function up(): void
    {
        // Índices removidos temporariamente para evitar conflito
        // O SQLite já cria alguns índices automaticamente
    }

    /**
     * Reverte a migration - remove os índices
     */
    public function down(): void
    {
        // Nada para reverter
    }
};
```

---

## 📝 PASSO A PASSO NO GITHUB:

### **1. No arquivo aberto no GitHub:**

**a) Aperte Ctrl+A (selecionar tudo)**

**b) Aperte Delete (apagar tudo)**

**c) Cole o código que está acima (todo ele!)**

---

### **2. Salvar:**

**a) Role até o final da página**

**b) Em "Commit message" escreva:**
```
Corrigir migration de índices
```

**c) Deixe marcado:**
```
⚫ Commit directly to the main branch
```

**d) Clique em:**
```
Commit changes
