# naut-cli

A cli client for SilverStripe's deploynaut.

## Install

With composer:

    composer global require guttmann/naut-cli
    
Ensure that `~/.composer/vendor/bin` is on your `PATH`.
    
## Usage

There are a few different commands that can be run.

### Configure

naut-cli relies on a configure file existing in your home directory.

You can create this by running:

    naut-cli configure
    
This will ask you a number of questions and then create a new file at `~/.naut.env`.

### Deploy

Deploy the latest commit of a project to a specific environment with deploy:

    naut-cli deploy <project_id> <environment> <branch_name>
    
Where:

- `<project_id>` is the short text identifier of the project in deploynaut (also seen in the deploynaut URL at `/naut/project/<project_id>`)
- `<environment>` is the name of the environment you want to deploy to (e.g. '`prod`', '`uat`, '`test1`')
- `<branch_name>` is the name of the git branch you would like to deploy

A few things will happen when you run this command:

1. Login to deploynaut with the configured credentials
2. Fetch latest changes from git
3. Trigger deployment
4. Stream the deployment log back to your terminal
