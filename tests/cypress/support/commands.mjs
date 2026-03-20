import { registerCommands } from 'joomla-cypress';

registerCommands();

Cypress.Commands.add('loginAdministrator', (username, password) => {
	if (!username || !password) {
		throw new Error('Missing Cypress admin credentials. Set CYPRESS_ADMIN_USER and CYPRESS_ADMIN_PASSWORD before running E2E tests.');
	}

	cy.visit('/administrator/');
	cy.contains('Joomla! 管理區登入').should('exist');
	cy.get('input[aria-label="用戶名"], input[name="username"]').first().clear().type(username);
	cy.get('input[aria-label="密碼"], input[type="password"]').first().clear().type(password, { log: false });
	cy.contains('button', '登錄').click();
	cy.contains('用戶名和密碼不匹配或者你還沒有帳戶。').should('not.exist');
	cy.contains('Joomla! 管理區登入').should('not.exist');
	cy.get('body', { timeout: 10000 }).should('not.contain.text', '登入表單');
});
