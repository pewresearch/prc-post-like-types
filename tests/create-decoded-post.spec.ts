import { test, expect } from '@wordpress/e2e-test-utils-playwright';

const testTitle = 'Test Decoded';
const testContent = 'This is a test Decoded blog post.';

test.describe('Create Decoded Post', () => {
	test('Ensure decoded post type is properly registered', async ({
		requestUtils,
	}) => {
		const decodedPosts = await requestUtils.rest({
			path: '/wp/v2/decoded',
			method: 'GET',
		});
		expect(decodedPosts).toBeDefined();
	});

	test('Short read post created', async ({ admin, editor, requestUtils }) => {
		await admin.createNewPost({
			title: testTitle,
			content: testContent,
			postType: 'decoded',
		});
		// Publish the decoded
		await editor.publishPost();

		// Get the created decoded via REST API
		const decodedPosts = await requestUtils.rest({
			path: '/wp/v2/decoded',
			method: 'GET',
		});
		// Get the first item out of the decodedPosts array
		const decodedPost = decodedPosts?.[0];
		// Verify the decoded was created with correct title and content
		expect(decodedPost.title.rendered).toBe(testTitle);
		expect(decodedPost.content.rendered).toContain(testContent);
	});
});
