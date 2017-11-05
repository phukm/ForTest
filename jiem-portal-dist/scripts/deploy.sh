temSource="/home/ec2-user/jiem-deploy/dist/"
webSource="/var/www/dantaitestr3"
workerSource="/home/testr3/sourcetestr3"
userSSH="ec2-user";
if [ "$DEPLOYMENT_GROUP_NAME" == "TestEnvironment" ]
then
    	userSSH="testr3"
	export APP_ENV=testr3
	webSource="/var/www/dantaitestr3"
	workerSource="/home/testr3/sourcetestr3"
elif [ "$DEPLOYMENT_GROUP_NAME" == "UATEnvironment" ];
then
    	userSSH="uatr3"
	export APP_ENV=uatr3
	webSource="/var/www/dantaiuatr3/"
	workerSource="/home/uatr3/sourceuatr3"
elif [ "$DEPLOYMENT_GROUP_NAME" == "PreProductionEnvironment" ];
then
    	userSSH="ec2-user"
	export APP_ENV=staging21
	webSource="/var/www/dantai2.1"
	workerSource="/home/ec2-user/source2.1"
fi

if [ -d "$webSource" ];
then
    cp -a "$temSource." "$webSource"
    cd "$webSource"
    sudo chown -Rf root:root *
    sudo chown -Rf apache:apache data
    #sudo chcon -R -t public_content_rw_t data/
    chmod 777 "$webSource/data/DoctrineORMModule/Proxy/"
elif [ -d "$workerSource" ];
then
	cp -a "$temSource." "$workerSource"	
    cd "$workerSource"
    chmod +x console/*.sh
    chmod 777 "$workerSource/data/DoctrineORMModule/Proxy/"
    $workerSource/doctrine-module orm:clear-cache:metadata > /dev/null
    $workerSource/doctrine-module orm:clear-cache:query > /dev/null
    $workerSource/doctrine-module orm:clear-cache:result > /dev/null
    $workerSource/doctrine-module orm:schema-tool:update --force
    chown -R $userSSH:$userSSH "$workerSource"
fi
