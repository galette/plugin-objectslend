---
title: Documentação
description: Object lending and renting
---

Este plugin oferece :

* gestão de objetos (descrição, tamanho, comprimento, preço, ...)
* Agrupa objetos por categorias,
* Gerenciar o estado dos objetos e sua presença em estoque ou não,
* Gerenciar o empréstimo e/ou aluguel de objetos,
* Geração de contribuições,
* ...

## Instalação

Primeiramente, baixe o plugin:

* [Get latest ObjectsLend
  plugin!](https://github.com/galette-plugins/plugin-objectslend/releases/latest)
* [Get ObjectsLend plugin nightly
  build!](https://github.com/galette-plugins/plugin-objectslend/releases/tag/nightly)

Extraia o arquivo baixado no diretório `plugins` do Galette. Por exemplo, no
Linux (substituindo `{url}` e `{version}` pelos valores corretos):

```bash
$ cd /var/www/html/galette/plugins
$ wget {url}
$ tar xjvf galette-plugin-objectslend-{version}.tar.bz2
```

## Inicialização do banco de dados

Para funcionar, este plugin requer várias tabelas no banco de dados. Consulte
[Interface de gerenciamento de plugins do
Galette](https://doc.galette.eu/en/master/plugins/index.html#plugins-managment).

E está concluído; o plugin ObjectsLend está instalado :)

## Plugin usage

Após a instalação do plugin, um grupo `Object lend` é adicionado ao menu
Galette.

As configurações padrão são fornecidas na instalação, mas podem não atender às
suas necessidades; você pode, naturalmente, definir as suas próprias.

![The list of object status](images/status.png)

Defina o status, crie categorias e objetos; os usuários podem emprestar objetos
com uma justificativa e, em seguida, devolvê-los com a localização.

Um histórico de empréstimos é fornecido para administradores e funcionários na
página do objeto.

### Preferências

Diversas preferências permitem alterar o comportamento do plugin.

![The plugin preferences](images/plugin_preferences.png)

![The images preferences](images/images_preferences.png)

![The display preferences](images/display_preferences.png)

Nessa tela, você pode definir se os membros podem emprestar objetos ou não, se
deve ser criada uma nova contribuição (e seu tipo e descrição), se a imagem deve
ser exibida na lista de objetos e o tamanho das miniaturas.

É possível ativar a exibição da foto em tamanho real.

> **Note** — Added in version 0.5.
> 
> As fotos enviadas com a versão anterior do plugin eram sempre redimensionadas,
> apenas a miniatura era armazenada. Se você quiser que as fotos sejam exibidas
> em tamanho original, será necessário enviá-las novamente.
