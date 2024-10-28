



symfony console doctrine:database:create -c auth --env=test
symfony console doctrine:database:create -c company
symfony console doctrine:database:create -c auth --env=test
symfony console doctrine:database:create -c company


symfony console doctrine:schema:update --force --em=auth
symfony console doctrine:schema:update --force --em=company
symfony console doctrine:schema:update --force --em=auth --env=test
symfony console doctrine:schema:update --force --em=company --env=test