import { test, expect } from '@wordpress/e2e-test-utils-playwright';

const testTitle = 'Test Engineering';
const testContent = 'This is a test Engineering blog post.';

test.describe('Create Engineering Post', () => {
	test('Ensure engineering post type is properly registered', async ({
		requestUtils,
	}) => {
		const engineeringPosts = await requestUtils.rest({
			path: '/wp/v2/engineering',
			method: 'GET',
		});
		expect(engineeringPosts).toBeDefined();
	});

	test('Short read post created', async ({ admin, editor, requestUtils }) => {
		await admin.createNewPost({
			title: testTitle,
			content: testContent,
			postType: 'engineering',
		});
		// Publish the engineering
		await editor.publishPost();

		// Get the created short read via REST API
		const engineeringPosts = await requestUtils.rest({
			path: '/wp/v2/engineering',
			method: 'GET',
		});
		// Get the first item out of the engineeringPosts array
		const engineeringPost = engineeringPosts?.[0];
		// Verify the engineering was created with correct title and content
		expect(engineeringPost.title.rendered).toBe(testTitle);
		expect(engineeringPost.content.rendered).toContain(testContent);
	});
});
