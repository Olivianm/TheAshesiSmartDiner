const request = require('supertest');
const app = require('../app'); // make sure this points to your app file

describe('POST /api/register', () => {
  it('should register a new user successfully', async () => {
    const res = await request(app)
      .post('/api/register')
      .send({
        name: 'Test User',
        email: 'testuser@example.com',
        password: 'password123'
      });

    expect(res.statusCode).toBe(201); // or 200 depending on your API
    expect(res.body).toHaveProperty('message');
    expect(res.body.message).toMatch(/registered/i);
  });
});
