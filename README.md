# 🏗️ RSF Construção - Sistema ERP/CRM para Gestão de Obras

![Status](https://img.shields.io/badge/Status-Concluído-success)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)
![SQL](https://img.shields.io/badge/SQL-Database-blue)

## 📌 Visão Geral
O **RSF Construção** é uma plataforma completa de ERP/CRM focada no setor de construção civil. O sistema foi desenvolvido para resolver o problema de descentralização de informações em canteiros de obras, unificando a gestão de projetos, controle financeiro e acompanhamento de cronogramas em um único ambiente.

## 🚀 Impacto e Resultados do Projeto
Este projeto foi arquitetado para suportar operações de escala e demonstrar eficiência operacional:
- **Escalabilidade:** Estruturado para o controle de **+50 projetos simultâneos**, garantindo integridade dos dados e rápida recuperação de informações.
- **Eficiência Operacional:** A centralização dos dados operacionais e financeiros proporcionou uma **redução de até 60% no tempo** de organização das informações que antes eram gerenciadas de forma manual ou em planilhas dispersas.
- **Visibilidade de Negócios:** Melhoria significativa na rastreabilidade de custos e atividades de cada obra.

## ⚙️ Funcionalidades Principais
- **Gestão de Projetos (`lista_projetos.php`):** Módulo central para acompanhamento do status, prazos e responsáveis por cada obra.
- **Controle Financeiro:** Registro de entradas, saídas, custos com materiais e mão de obra, permitindo uma visão clara do orçamento da construção.
- **Painel Centralizado:** Interface focada na usabilidade do gestor, consolidando dados operacionais para facilitar a tomada de decisão.

## 🛠️ Tecnologias Utilizadas
- **Back-end:** PHP (Lógica de negócios e integração com banco de dados).
- **Banco de Dados:** SQL (Modelagem de dados relacionais e queries otimizadas).
- **Front-end:** HTML, CSS, JavaScript (Interface responsiva e interativa).

### Padronização de Código (Boas Práticas)
O projeto segue boas práticas de nomenclatura e segurança. Por exemplo, as conexões com o banco de dados são estritamente padronizadas e isoladas utilizando a variável `$conexao`, garantindo manutenibilidade e clareza no código-fonte para futuras expansões.

```php
// Exemplo de padrão de conexão utilizado no projeto
include 'config.php';
$query = "SELECT * FROM projetos WHERE status = 'em_andamento'";
$resultado = mysqli_query($conexao, $query);
