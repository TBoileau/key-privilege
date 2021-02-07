# Note de cadrage

[Retour au sommaire](index.md)

## Première expression de besoin

```text
Prérequis :
- Gestion et affichage d’une boutique de lots
    - UX performant et responsive
    - Critères de filtre et de recherche multiple
- Rôle et privilèges d’accès : Admin LADR, Admin Groupement, Admin Indépendant, Commercial Adhérent, Garagiste-Carrossier, Collaborateur Adhérent
- Accès Backoffice pour gestion CRUD des bénéficiaires ; pour les accès : Admin LADR, Admin Groupement, Admin Indépendant
- Accès Backoffice pour paramétrage d’affichage des boutiques selon typologie des adhérents ; uniquement pour l’accès Admin LADR
- Gestion des achats de clés et du suivi de facturation
- Gestion des mouvements de clés : crédit, débit, retrait
- Suivi logistique des commandes de lots
- Ajouter la possibilité pour chaque bénéficiaire d’un lot de déclencher une demande de SAV
- A date, plusieurs des fonctionnalités ci-dessus ont été développées mais ne sont pas administrables et consultables facilement. C’est un casse-tête d’ajouter un adhérent par exemple. C’est également impossible de suivre facilement les mouvements de clés pour un admin.
```
## Q/R

```text
Boutique de lots :
    - Pour la gestion des boutiques par adhérent, est-ce que cela veut dire que l'on peut filtrer les lots affichés en fonction de l'adhérent ? => Oui. Actuellement on le fait en bricolant une condition faite par Gui pour que les clients bénéficiaires de l’adhérent Ouest Injection soient les seuls à voir en boutique les chèques-cadeaux. Je ne sais pas si ce sera côté back-office LADR (Speculoos) qu’il faudra pouvoir le paramétrer ou côté back-office du projet KP sur l’accès de l’équipe projet, mais par expérience je sais que c’est une nécessité de savoir gérer/décliner des boutiques différentes selon les attentes de chaque adhérent.
    - Et est-ce que la boutique de lot filtrée par adhérent se répercute chez le client de l'adhérent ? => Je crois que tu as la réponse dans ce que j’ai répondu précédemment.
    - Exemple de la boutique ultime (UX/UI) => Est-ce que tu attends là que Tim te prépare la maquette souhaitée ?

Typologie de société :
    - Groupement > Adhérent/Indépendant > Client (Garagiste/carrossier)

Je crois qu’on n’arrive pas à avoir le bon vocabulaire commun pour se comprendre, mais on va y arriver. Voici une nouvelle tentative pour lister le type de sociétés :
    - Adhérent multi-entrepôts. Exemple actuel : Aurélie Jeannette qui doit gérer via différents accès les raisons sociales : IDLP, NED, Choisy Pièces Auto. En définitive, elle devrait pouvoir n’avoir qu’un seul et unique accès.
    - Adhérent mono-entrepôt. Exemple actuel : Ouest Injection
    -  Client (garagiste/carrossier) : ce sont les bénéficiaires du programme inscrits par les 2 types d’adhérent prélistés
Rôles utilisateur :
    - Administrateur LADR
    - Administrateur adhérent multi-entrepôts
    - Administrateur adhérent mono-entrepôt
    - Commercial : Actuellement son rôle n'est que du monitoring, pas d'action, envisage-t-on de lui étendre son rôle ? => Oui, on doit pouvoir lui permettre de bénéficier de 2 options : inscrire un client (par défaut il ne le peut pas, c’est réservé à l’administrateur) + bénéficier d’un compte-clés personnel (par défaut il n’en a pas, c’est son administrateur qui pourrait décider de se servir de la boutique KP pour récompenser aussi ses commerciaux en leur offrant des clés)
    - Collaborateur : Que veut dire collaborateur ? Rôle de visu ? => Non, pas un rôle de visu. Pour moi c’est plutôt un statut « joker » qui nous manque sur de nombreux projets quand nos donneurs d’ordre nous disent : on aimerait ouvrir un accès à Jeannine de la compta et lui créditer 100 clés pour qu’elle passe commande en boutique car elle a bien bossé. Mais Jeannine n’est ni une cliente (rôle garagiste/carrossier), ni une commerciale (rôle commercial). Du coup on se retrouve à bricoler une rustine. Le rôle collaborateur jouerait ce rôle manquant pour permettre à des bénéficiaires de se voir ouvrir un accès sans logique de rattachement particulier (un client à un commercial).

Pas d'admin adhérent ? => Ajouté à ton listing ci-dessus.

Au final, quelle est la réelle différence entre adhérent et indépendant, si ce n'est sa typologie ? => Mes précédentes réponses devraient avoir répondu à cette question qui n’a pas vraiment de sens si on utilise désormais les mêmes termes pour désigner un adhérent selon sa typologie de multi ou mono entrepôt.

Doit-on prévoir l'achat avec CB ? Si oui, Stripe ? Lemonway ? Paypal ? Ou la banque ? ... Ou reste-t-on sur le couple virement/chèque, et donc quelque chose de manuel ? => Pour l’instant, on reste sur le fonctionnement actuel.

Si manuel, prévoir dans le back office une gestion de la facturation avec crédit de clé ? => Oui, on reste comme ça.

Quelle est la mécanique du SAV ? Envoi d'email ou mode opératoire sur le site ? => On reste sur qqch de « simple » côté client avec l’envoi d’un email chez nous. Par contre, à la main, l’idée est de pouvoir donner un statut dans notre accès back-office aux demandes reçues (demande en cours de traitement, demande clôturée, en attente de réponse, non pris en charge par le SAV,…) et que l’utilisateur puisse consulter via son accès un « suivi des demandes SAV » au même titre qu’il aura un « suivi des commandes de lots ».
```
