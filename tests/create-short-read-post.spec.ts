import { test, expect } from '@wordpress/e2e-test-utils-playwright';

const testTitle = 'Test Short Read';
const testContent = 'This is a test Short Reads blog post.';

test.describe('Create Short Read Post', () => {
	test('Ensure short-read post type is properly registered', async ({
		requestUtils,
	}) => {
		const shortReadPosts = await requestUtils.rest({
			path: '/wp/v2/short-read',
			method: 'GET',
		});
		expect(shortReadPosts).toBeDefined();
	});

	test('Short read post created', async ({ admin, editor, requestUtils }) => {
		await admin.createNewPost({
			title: testTitle,
			content: testContent,
			postType: 'short-read',
		});
		// Publish the short read
		await editor.publishPost();

		// Get the created short read via REST API
		const shortReadPosts = await requestUtils.rest({
			path: '/wp/v2/short-read',
			method: 'GET',
		});
		// Get the first item out of the shortReadPosts array
		const shortReadPost = shortReadPosts?.[0];
		// Verify the short read was created with correct title and content
		expect(shortReadPost.title.rendered).toBe(testTitle);
		expect(shortReadPost.content.rendered).toContain(testContent);
	});
});
